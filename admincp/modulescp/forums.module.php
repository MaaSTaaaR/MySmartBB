<?php
(!defined('IN_PowerBB')) ? die() : '';
define('IN_ADMIN',true);

define('CLASS_NAME','PowerBBForumsMOD');

include('../common.php');
class PowerBBForumsMOD extends _functions
{
	function run()
	{
		global $PowerBB;

		if ($PowerBB->_CONF['member_permission'])
		{
			$PowerBB->template->display('header');

			if ($PowerBB->_CONF['rows']['group_info']['admincp_section'] == '0')
			{
			  $PowerBB->functions->error($PowerBB->_CONF['template']['_CONF']['lang']['error_permission']);
			}

			if ($PowerBB->_GET['add'])
			{
				if ($PowerBB->_GET['main'])
				{
					$this->_AddMain();
				}
				elseif ($PowerBB->_GET['start'])
				{
					$this->_AddStart();
				}
			}
			elseif ($PowerBB->_GET['control'])
			{
				if ($PowerBB->_GET['main'])
				{
					$this->_ControlMain();
				}
			}
			elseif ($PowerBB->_GET['edit'])
			{
				if ($PowerBB->_GET['main'])
				{
					$this->_EditMain();
				}
				elseif ($PowerBB->_GET['start'])
				{
					$this->_EditStart();
				}
			}
			elseif ($PowerBB->_GET['del'])
			{
				if ($PowerBB->_GET['main'])
				{
					$this->_DelMain();
				}
				elseif ($PowerBB->_GET['start'])
				{
					$this->_DelStart();
				}
			}
			elseif ($PowerBB->_GET['change_sort'])
			{
				$this->_ChangeSort();
			}
			elseif ($PowerBB->_GET['groups'])
			{
				if ($PowerBB->_GET['control_group'])
				{
					if ($PowerBB->_GET['index'])
					{
						$this->_GroupControlMain();
					}
					if ($PowerBB->_GET['start'])
					{
						$this->_GroupControlStart();
					}
				}
			}
			elseif ($PowerBB->_GET['forum'])
			{
				if ($PowerBB->_GET['index'])
				{
					$this->_ForumMain();
				}
			}

			$PowerBB->template->display('footer');
		}
	}

	function _AddMain()
	{
	global $PowerBB;

		// Show Jump List to:)
		$Master = array();
		$Master = $PowerBB->section->GetSectionsList(array ('id'=>$id,'title'=>"".$title."",'parent'=>$parent));
		$MainAndSub = new PowerBBCommon;
        $PowerBB->template->assign('DoJumpList',$MainAndSub->DoJumpList($Master,false,1));
		unset($Master);
	   ////////

		$GroupArr 						= 	array();
		$GroupArr['order'] 				= 	array();
		$GroupArr['order']['field'] 	= 	'id';
		$GroupArr['order']['type'] 		= 	'ASC';

		$PowerBB->_CONF['template']['while']['groups'] = $PowerBB->core->GetList($GroupArr,'group');

		//////////

		$PowerBB->template->display('forum_add');
	}

	function _AddStart()
	{
		global $PowerBB;

		//////////


 		if (empty($PowerBB->_POST['name'])
 			or ($PowerBB->_POST['order_type'] == 'manual' and empty($PowerBB->_POST['sort'])))
		{
			$PowerBB->functions->error($PowerBB->_CONF['template']['_CONF']['lang']['Please_fill_in_all_the_information']);
		}

		//////////

		$sort = 0;

		if ($PowerBB->_POST['order_type'] == 'auto')
		{
			$SortArr = array();
			$SortArr['where'] = array('parent',$PowerBB->_POST['section']);
			$SortArr['order'] = array();
			$SortArr['order']['field'] = 'sort';
			$SortArr['order']['type'] = 'DESC';

			$SortSection = $PowerBB->core->GetInfo($SortArr,'section');

			// No section
			if (!$SortSection)
			{
				$sort = 1;
			}
			// There is a section
			else
			{
				$sort = $SortSection['sort'] + 1;
			}
		}
		else
		{
			$sort = $PowerBB->_POST['sort'];
		}

		//////////


		$SecArr 			= 	array();
		$SecArr['field']	=	array();

		$SecArr['field']['title'] 					= 	$PowerBB->_POST['name'];
		$SecArr['field']['sort'] 					= 	$sort;
		$SecArr['field']['section_describe']		=	$PowerBB->_POST['describe'];
		$SecArr['field']['parent']					=	$PowerBB->_POST['section'];
		$SecArr['field']['forum_title_color']	   =	$PowerBB->_POST['forum_title_color'];
		$SecArr['field']['show_sig']				=	$PowerBB->_POST['show_sig'];
		$SecArr['field']['use_power_code_allow']	=	$PowerBB->_POST['use_power_code_allow'];
		$SecArr['field']['section_picture']			=	$PowerBB->_POST['section_picture'];
		$SecArr['field']['sectionpicture_type']		=	$PowerBB->_POST['sectionpicture_type'];
		$SecArr['field']['use_section_picture']		=	$PowerBB->_POST['use_section_picture'];
		$SecArr['field']['linksection']				=	$PowerBB->_POST['linksection'];
		$SecArr['field']['linksite']				=	$PowerBB->_POST['linksite'];
		$SecArr['field']['subject_order']			=	$PowerBB->_POST['subject_order'];
		$SecArr['field']['hide_subject']			=	$PowerBB->_POST['hide_subject'];
		$SecArr['field']['sec_section']				=	$PowerBB->_POST['sec_section'];
		$SecArr['field']['header'] 					= 	$PowerBB->_POST['head'];
		$SecArr['field']['footer'] 					= 	$PowerBB->_POST['foot'];
		$SecArr['field']['active_prefix_subject'] 	= 	$PowerBB->_POST['active_prefix_subject'];
		$SecArr['field']['prefix_subject'] 			= 	$PowerBB->_POST['prefix_subject'];
		$SecArr['field']['sig_iteration']			=	$PowerBB->_POST['sig_iteration'];
		$SecArr['field']['review_subject']			=	$PowerBB->_POST['review_subject'];

		$SecArr['get_id']							=	true;

		$insert = $PowerBB->section->InsertSection($SecArr);

		//////////

		if ($insert)
		{
			//////////

		$SecArr 					= 	array();
		$SecArr['get_from']			=	'db';
		$SecArr['proc'] 			= 	array();
		$SecArr['proc']['*'] 		= 	array('method'=>'clean','param'=>'html');
		$SecArr['order']			=	array();
		$SecArr['order']['field']	=	'sort';
		$SecArr['order']['type']	=	'ASC';

		$SecArr['where']				=	array();
		$SecArr['where'][0]				=	array();
		$SecArr['where'][0]['name']		=	'parent';
		$SecArr['where'][0]['oper']		=	'<>';
		$SecArr['where'][0]['value']	=	'0';

		$SecList = $PowerBB->section->GetSectionsList($SecArr);

		$x = 0;
		$y = sizeof($SecList);
		$s = array();

		while ($x < $y)
		{
			$name = 'order-' . $SecList[$x]['id'];

			if ($SecList[$x]['order'] != $PowerBB->_POST[$name])
			{
				$UpdateArr 						= 	array();

				$UpdateArr['field']		 		= 	array();
				$UpdateArr['field']['sort'] 	= 	$PowerBB->_POST[$name];

				$UpdateArr['where'] 			=	array('id',$SecList[$x]['id']);

				$update = $PowerBB->core->Update($UpdateArr,'section');

				if ($update)
				{
					$cache = $PowerBB->section->UpdateSectionsCache(array('parent'=>$SecList[$x]['parent']));
					$cache = $PowerBB->section->UpdateSectionsCache(array('parent'=>$SecList[$x]['id']));
				}

				$s[$SecList[$x]['id']] = ($update) ? 'true' : 'false';
			}

			$x += 1;
		}

		if (in_array('false',$s))
		{
			$PowerBB->functions->error($PowerBB->_CONF['template']['_CONF']['lang']['did_not_succeed_the_process']);
		}


			$GroupArr 						= 	array();
			$GroupArr['order'] 				= 	array();
			$GroupArr['order']['field'] 	= 	'id';
			$GroupArr['order']['type'] 		= 	'ASC';

			$groups = $PowerBB->group->GetGroupList($GroupArr);

			//////////

			$x = 0;
			$n = sizeof($groups);

			while ($x < $n)
			{
				$SecArr 			= 	array();
				$SecArr['field']	=	array();

				$SecArr['field']['section_id'] 			= 	$PowerBB->section->id;
				$SecArr['field']['group_id'] 			= 	$groups[$x]['id'];
				$SecArr['field']['view_section'] 		= 	$PowerBB->_POST['groups'][$groups[$x]['id']]['view_section'];
				$SecArr['field']['view_subject'] 		= 	$PowerBB->_POST['groups'][$groups[$x]['id']]['view_subject'];
				$SecArr['field']['download_attach'] 	= 	$groups[$x]['download_attach'];
				$SecArr['field']['write_subject'] 		= 	$PowerBB->_POST['groups'][$groups[$x]['id']]['write_subject'];
				$SecArr['field']['write_reply'] 		= 	$PowerBB->_POST['groups'][$groups[$x]['id']]['write_reply'];
				$SecArr['field']['upload_attach'] 		= 	$groups[$x]['upload_attach'];
				$SecArr['field']['edit_own_subject']	= 	$groups[$x]['edit_own_subject'];
				$SecArr['field']['edit_own_reply'] 		= 	$groups[$x]['edit_own_reply'];
				$SecArr['field']['del_own_subject'] 	= 	$groups[$x]['del_own_subject'];
				$SecArr['field']['del_own_reply'] 		= 	$groups[$x]['del_own_reply'];
				$SecArr['field']['write_poll'] 			= 	$groups[$x]['write_poll'];
				$SecArr['field']['no_posts'] 			= 	$groups[$x]['no_posts'];
				$SecArr['field']['vote_poll'] 			= 	$groups[$x]['vote_poll'];
				$SecArr['field']['main_section'] 		= 	0;
				$SecArr['field']['group_name'] 			= 	$groups[$x]['title'];

				$insert = $PowerBB->group->InsertSectionGroup($SecArr);

				unset($SecArr);

				if ($insert)
				{
					$success[] = $id;
				}
				else
				{
					$fail[] = $id;
				}

				unset($insert);

					$x += 1;
			}

			//////////


			$CacheArr 			= 	array();
			$CacheArr['id'] 	= 	$PowerBB->section->id;

			$cache = $PowerBB->group->UpdateSectionGroupCache($CacheArr);

       		$SecArr 			= 	array();
		    $SecArr['where'] 	= 	array('id',$PowerBB->_POST['section']);

		    $SectionInfo = $PowerBB->core->GetInfo($SecArr,'section');

			$cache = $PowerBB->section->UpdateSectionsCache(array('parent'=>$PowerBB->_POST['section']));
            $cache = $PowerBB->section->UpdateSectionsCache(array('parent'=>$SectionInfo['parent']));
            $cache = $PowerBB->section->UpdateSectionsCache(array('id'=>$PowerBB->section->id));
			$cache = $PowerBB->section->UpdateSectionsCache(array('id'=>$PowerBB->_POST['section']));

			//////////

                $SecArr 					= 	array();
				$SecArr['get_from']			=	'db';
				$SecArr['proc'] 			= 	array();
				$SecArr['proc']['*'] 		= 	array('method'=>'clean','param'=>'html');
				$SecArr['order']			=	array();
				$SecArr['order']['field']	=	'sort';
				$SecArr['order']['type']	=	'ASC';

				$SecArr['where']				=	array();
				$SecArr['where'][0]				=	array();
				$SecArr['where'][0]['name']		=	'parent';
				$SecArr['where'][0]['oper']		=	'<>';
				$SecArr['where'][0]['value']	=	'0';

				$SecList = $PowerBB->core->GetList($SecArr,'section');

				$x = 0;
				$y = sizeof($SecList);
				$s = array();

				while ($x < $y)
				{
					$name = 'order-' . $SecList[$x]['id'];

					if ($SecList[$x]['order'] != $PowerBB->_POST[$name])
					{
						$UpdateArr 						= 	array();

						$UpdateArr['field']		 		= 	array();
						$UpdateArr['field']['sort'] 	= 	$PowerBB->_POST[$name];

						$UpdateArr['where'] 			=	array('id',$SecList[$x]['id']);

						$update = $PowerBB->core->Update($UpdateArr,'section');

						if ($update)
						{
							$cache = $PowerBB->section->UpdateSectionsCache(array('parent'=>$SecList[$x]['parent']));
							$cache = $PowerBB->section->UpdateSectionsCache(array('parent'=>$PowerBB->_POST['section']));
						}

						$s[$SecList[$x]['id']] = ($update) ? 'true' : 'false';
					}

					$x += 1;
				}

				if (in_array('false',$s))
				{
					$PowerBB->functions->error($PowerBB->_CONF['template']['_CONF']['lang']['did_not_succeed_the_process']);
				}

						$PowerBB->functions->msg($PowerBB->_CONF['template']['_CONF']['lang']['Forum_has_been_added_successfully']);
						$PowerBB->functions->redirect('index.php?page=forums&amp;edit=1&amp;main=1&amp;id=' . $PowerBB->section->id);

		}
		else
		{
			$PowerBB->functions->error($PowerBB->_CONF['template']['_CONF']['lang']['not_able_to_add_Section']);
		}
	}

	function _ControlMain()
	{
		global $PowerBB;

		//////////

		$SecArr 						= 	array();
		$SecArr['get_from']				=	'db';

		$SecArr['proc'] 				= 	array();
		$SecArr['proc']['*'] 			= 	array('method'=>'clean','param'=>'html');

		$SecArr['order']				=	array();
		$SecArr['order']['field']		=	'sort';
		$SecArr['order']['type']		=	'ASC';

		$SecArr['where']				=	array();
		$SecArr['where'][0]['name']		= 	'parent';
		$SecArr['where'][0]['oper']		= 	'=';
		$SecArr['where'][0]['value']	= 	'0';

		// Get main sections
		$cats = $PowerBB->core->GetList($SecArr,'section');

		// We will use forums_list to store list of forums which will view in main page
		$PowerBB->_CONF['template']['foreach']['forums_list'] = array();

		// Loop to read the information of main sections
       foreach ($cats as $cat)
		{
	               // foreach main sections
                    unset($sectiongroup);

	                   // Get main Forums
						$ForumArr 						= 	array();
						$ForumArr['get_from']				=	'db';

						$ForumArr['proc'] 				= 	array();
						$ForumArr['proc']['*'] 			= 	array('method'=>'clean','param'=>'html');

						$ForumArr['order']				=	array();
						$ForumArr['order']['field']		=	'sort';
						$ForumArr['order']['type']		=	'ASC';

						$ForumArr['where']				=	array();
						$ForumArr['where'][0]['name']		= 	'parent';
						$ForumArr['where'][0]['oper']		= 	'=';
						$ForumArr['where'][0]['value']	= 	$cat['id'];

						// Get parent sections
						$forums = $PowerBB->core->GetList($ForumArr,'section');

					foreach ($forums as $forum)
					{
						//////////////////////////

							$forum['is_sub'] 	= 	0;
							$forum['sub']		=	'';
                              @include("../cache/forums_cache/forums_cache_".$forum['id'].".php");
                               if (!empty($forums_cache))
	                           {

									$subs = json_decode(base64_decode($forums_cache), true);
	                               foreach ($subs as $sub)
									{
									   if ($forum['id'] == $sub['parent'])
	                                    {

												if (!$forum['is_sub'])
												{
													$forum['is_sub'] = 1;
												}
												 $forum['sub'] .= ('<option value="' .$sub['id'] . '">---- '  . $sub['title'] . '</option>');

										  }

					                         ///////////////

													$forum['is_sub_sub'] 	= 	0;
													$forum['sub_sub']		=	'';

				                              @include("../cache/forums_cache/forums_cache_".$sub['id'].".php");

		                                   if (!empty($forums_cache))
				                           {

												$subs_sub = json_decode(base64_decode($forums_cache), true);
				                               foreach ($subs_sub as $sub_sub)
												{
												   if ($sub['id'] == $sub_sub['parent'])
				                                    {

																	if (!$forum['is_sub_sub'])
																	{
																		$forum['is_sub_sub'] = 1;
																	}

															 $forum['sub_sub'] .= ('<option value="' .$sub_sub['id'] . '">---- '  . $sub_sub['title'] . '</option>');
													  }
												 }

										   }
									 }
								}


							$PowerBB->_CONF['template']['foreach']['forums_list'][$forum['id'] . '_f'] = $forum;

		             } // end foreach ($forums)

						unset($ForumArr);
			            $ForumArr = $PowerBB->DB->sql_free_result($ForumArr);
          }

    		 unset($SecArr);
			 $SecArr = $PowerBB->DB->sql_free_result($SecArr);

		//////////
		$PowerBB->template->display('forums_main');
	}

	function _EditMain()
	{
		global $PowerBB;

		//////////
		$PowerBB->_CONF['template']['Inf'] = false;
		$this->check_by_id($PowerBB->_CONF['template']['Inf']);
		$PowerBB->_GET['id'] = $PowerBB->functions->CleanVariable($PowerBB->_GET['id'],'intval');

		// Show Jump List to:)
		$Master = array();
		$Master = $PowerBB->section->GetSectionsList(array ('id'=>$id,'title'=>"".$title."",'parent'=>$parent));
		$MainAndSub = new PowerBBCommon;
        $PowerBB->template->assign('DoJumpList',$MainAndSub->DoJumpList($Master,false,1));
		unset($Master);
	   ////////

		$PowerBB->template->display('forum_edit');
	}

	function _EditStart()
	{
		global $PowerBB;

		//////////

		$PowerBB->_CONF['template']['Inf'] = false;

		$this->check_by_id($PowerBB->_CONF['template']['Inf']);

		//////////

 		if (empty($PowerBB->_POST['name']))
		{
			$PowerBB->functions->error($PowerBB->_CONF['template']['_CONF']['lang']['no_Forum_title']);
		}

 		if (empty($PowerBB->_POST['sort']))
		{
			$PowerBB->functions->error($PowerBB->_CONF['template']['_CONF']['lang']['no_Forum_Order']);
		}

		//////////

		// Check if the user change the parent or not
		$new_parent 	= 	false;
		$old_parent		=	0;

		if ($PowerBB->_CONF['template']['Inf']['parent'] != $PowerBB->_POST['parent'])
		{
			$new_parent		= 	true;
			$old_parent		=	$PowerBB->_CONF['template']['Inf']['parent'];
		}

		//////////
		$SecArr 			= 	array();
		$SecArr['field']	=	array();

		$SecArr['field']['title'] 					= 	$PowerBB->_POST['name'];
		$SecArr['field']['sort'] 					= 	$PowerBB->_POST['sort'];
		$SecArr['field']['section_describe']		=	$PowerBB->_POST['describe'];
		$SecArr['field']['section_password']		=	$PowerBB->_POST['section_password'];
		$SecArr['field']['show_sig']				=	$PowerBB->_POST['show_sig'];
		$SecArr['field']['use_power_code_allow']	=	$PowerBB->_POST['use_power_code_allow'];
		$SecArr['field']['section_picture']			=	$PowerBB->_POST['section_picture'];
		$SecArr['field']['sectionpicture_type']		=	$PowerBB->_POST['sectionpicture_type'];
		$SecArr['field']['use_section_picture']		=	$PowerBB->_POST['use_section_picture'];
		$SecArr['field']['linksection']				=	$PowerBB->_POST['linksection'];
		$SecArr['field']['linksite']				=	$PowerBB->_POST['linksite'];
		$SecArr['field']['subject_order']			=	$PowerBB->_POST['subject_order'];
		$SecArr['field']['hide_subject']			=	$PowerBB->_POST['hide_subject'];
		$SecArr['field']['sec_section']				=	$PowerBB->_POST['sec_section'];
		$SecArr['field']['header'] 					= 	$PowerBB->_POST['head'];
		$SecArr['field']['footer'] 					= 	$PowerBB->_POST['foot'];
		$SecArr['field']['active_prefix_subject'] 	= 	$PowerBB->_POST['active_prefix_subject'];
		$SecArr['field']['prefix_subject'] 			= 	$PowerBB->_POST['prefix_subject'];
		$SecArr['field']['sig_iteration']			=	$PowerBB->_POST['sig_iteration'];
		$SecArr['field']['parent']					=	$PowerBB->_POST['parent'];
		$SecArr['field']['review_subject']			=	$PowerBB->_POST['review_subject'];
		$SecArr['field']['forum_title_color']	   =	$PowerBB->_POST['forum_title_color'];
		$SecArr['where']							= 	array('id',$PowerBB->_CONF['template']['Inf']['id']);

		$update = $PowerBB->core->Update($SecArr,'section');

		if ($update)
		{
            // check sec_subject 1 >> hide all subjects
            $section = $PowerBB->_CONF['template']['Inf']['id'];
		     if ($PowerBB->_POST['sec_section'] == 1
		     or $PowerBB->_POST['hide_subject'] == 1)
			 {
				$SubjectArrQuery = $PowerBB->DB->sql_query("SELECT * FROM " . $PowerBB->table['subject'] . " WHERE section = '$section' and sec_subject = 0 ");
				if($SubjectArrQuery)
				 {
					while ($getSubject_row = $PowerBB->DB->sql_fetch_array($SubjectArrQuery))
					{
					$SubjectArr = array();
					$SubjectArr['field'] = array();
					$SubjectArr['field']['sec_subject'] = '1';

					$SubjectArr['where'] = array('id',$getSubject_row['id']);

					$Update = $PowerBB->core->Update($SubjectArr,'subject');
					}
			     }
			 }

			$cache = $PowerBB->section->UpdateSectionsCache(array('parent'=>$PowerBB->_POST['parent']));
           	$cache = $PowerBB->section->UpdateSectionsCache(array('id'=>$PowerBB->_POST['parent']));
     		$UpdateArr 				= 	array();
     		$UpdateArr['parent'] 	= 	$PowerBB->_CONF['template']['Inf']['parent'];

     		$update_cache = $PowerBB->section->UpdateSectionsCache($UpdateArr);

			$cache = $PowerBB->section->UpdateSectionsCache(array('id'=>$PowerBB->_CONF['template']['Inf']['id']));

			// There is a new main section
			if ($new_parent)
			{
			  $PowerBB->functions->_AllCacheStart();
			}


			$PowerBB->functions->msg($PowerBB->_CONF['template']['_CONF']['lang']['Forum_has_been_updated_successfully']);
			$PowerBB->functions->redirect('index.php?page=forums&amp;edit=1&amp;main=1&amp;id=' . $PowerBB->_CONF['template']['Inf']['id']);
        }
	}

	function _DelMain()
	{
		global $PowerBB;

		$PowerBB->_CONF['template']['Inf'] = false;

		$this->check_by_id($PowerBB->_CONF['template']['Inf']);


		//////////

        $SecArr 						= 	array();
		$SecArr['get_from']				=	'db';

		$SecArr['proc'] 				= 	array();
		$SecArr['proc']['*'] 			= 	array('method'=>'clean','param'=>'html');

		$SecArr['order']				=	array();
		$SecArr['order']['field']		=	'sort';
		$SecArr['order']['type']		=	'ASC';

		$SecArr['where']				=	array();
		$SecArr['where'][0]['name']		= 	'parent';
		$SecArr['where'][0]['oper']		= 	'=';
		$SecArr['where'][0]['value']	= 	'0';

		// Get main sections
		$cats = $PowerBB->core->GetList($SecArr,'section');

		// We will use forums_list to store list of forums which will view in main page
		$PowerBB->_CONF['template']['foreach']['forums_list'] = array();

		// Loop to read the information of main sections
		foreach ($cats as $cat)
		{
			// Get the groups information to know view this section or not
			$groups = json_decode(base64_decode($cat['sectiongroup_cache']), true);


					$PowerBB->_CONF['template']['foreach']['forums_list'][$cat['id'] . '_m'] = $cat;


			if (!empty($cat['forums_cache']))
			{
				$forums = json_decode(base64_decode($cat['forums_cache']), true);

				foreach ($forums as $forum)
				{


							$forum['is_sub'] 	= 	0;
							$forum['sub']		=	'';

							if (!empty($forum['forums_cache']))
							{
								$subs = json_decode(base64_decode($forum['forums_cache']), true);

								if (is_array($subs))
								{
									foreach ($subs as $sub)
									{

												if (!$forum['is_sub'])
												{
													$forum['is_sub'] = 1;
												}

												$forum['sub'] .= ('<option value="' .$sub['id'] . '">---'  . $sub['title'] . '</option>');

									}
								}
							}


							$PowerBB->_CONF['template']['foreach']['forums_list'][$forum['id'] . '_f'] = $forum;
					} // end if is_array
				} // end foreach ($forums)
			} // end !empty($forums_cache)

		// Show Jump List to:)
		$Master = array();
		$Master = $PowerBB->section->GetSectionsList(array ('id'=>$id,'title'=>"".$title."",'parent'=>$parent));
		$MainAndSub = new PowerBBCommon;
        $PowerBB->template->assign('DoJumpList',$MainAndSub->DoJumpList($Master,false,1));
		unset($Master);
	   ////////

		$PowerBB->template->display('forum_del');
	}

	function _DelStart()
	{
		global $PowerBB;

		$PowerBB->_CONF['template']['Inf'] = false;

		$this->check_by_id($PowerBB->_CONF['template']['Inf']);

		if ($PowerBB->_POST['choose'] == 'move')
		{
			$DelArr 			= 	array();
			$DelArr['where'] 	= 	array('id',$PowerBB->_CONF['template']['Inf']['id']);

			$del = $PowerBB->core->Deleted($DelArr,'section');

			if ($del)
			{
				$PowerBB->functions->msg($PowerBB->_CONF['template']['_CONF']['lang']['Forum_has_been_deleted_successfully']);

				$move = $PowerBB->subject->MassMoveSubject(array('to'=>$PowerBB->_POST['section'],'from'=>$PowerBB->_CONF['template']['Inf']['id']));

				if ($move)
				{
					$PowerBB->functions->msg($PowerBB->_CONF['template']['_CONF']['lang']['Topic_has_been_moved_successfully']);

					//////////

					$NumberArr 				= 	array();
					$NumberArr['get_from']	=	'db';
					$NumberArr['where'] 	= 	array('section',$PowerBB->_CONF['template']['Inf']['id']);

					$FromSubjectNumber = $PowerBB->core->GetNumber($NumberArr,'subject');

					unset($NumberArr);

					//////////

					$NumberArr 				= 	array();
					$NumberArr['get_from']	=	'db';
					$NumberArr['where'] 	= 	array('section',$PowerBB->_POST['section']);

					$ToSubjectNumber = $PowerBB->core->GetNumber($NumberArr,'subject');

					//////////

					$ReplyNumberArr 				= 	array();
					$ReplyNumberArr['get_from']	=	'db';
					$ReplyNumberArr['where'] 	= 	array('section',$PowerBB->_CONF['template']['Inf']['id']);

					$FromReplyNumber = $PowerBB->core->GetNumber($ReplyNumberArr,'reply');

					unset($ReplyNumberArr);

					//////////

					$ReplyNumberArrTo 				= 	array();
					$ReplyNumberArrTo['get_from']	=	'db';
					$ReplyNumberArrTo['where'] 	= 	array('section',$PowerBB->_POST['section']);

					$ToReplyNumber = $PowerBB->core->GetNumber($ReplyNumberArrTo,'reply');

					//////////

			        $InfSectionID = $PowerBB->_CONF['template']['Inf']['id'];
				    $sql_Section = $PowerBB->DB->sql_query("SELECT  *   FROM " . $PowerBB->table['section'] . " WHERE parent = '$InfSectionID' ");

				       while ($getSection_row = $PowerBB->DB->sql_fetch_array($sql_Section))
				      {

			     		    $UpdateArr 					= 	array();
			   				$UpdateArr['field']			=	array();

			   				$UpdateArr['field']['parent'] 	= 	$PowerBB->_POST['section'];
			   				$UpdateArr['where']					= 	array('parent',$getSection_row['parent']);

			     		    $update = $PowerBB->core->Update($UpdateArr,'section');

		               }

					$SecArr 					= 	array();
					$SecArr['get_from']			=	'db';
					$SecArr['proc'] 			= 	array();
					$SecArr['proc']['*'] 		= 	array('method'=>'clean','param'=>'html');
					$SecArr['order']			=	array();
					$SecArr['order']['field']	=	'sort';
					$SecArr['order']['type']	=	'ASC';

					$SecArr['where']				=	array();
					$SecArr['where'][0]				=	array();
					$SecArr['where'][0]['name']		=	'parent';
					$SecArr['where'][0]['oper']		=	'<>';
					$SecArr['where'][0]['value']	=	'0';

					$SecList = $PowerBB->core->GetList($SecArr,'section');

					$x = 0;
					$y = sizeof($SecList);
					$s = array();

					while ($x < $y)
					{
						$name = 'order-' . $SecList[$x]['id'];

						if ($SecList[$x]['order'] != $PowerBB->_POST[$name])
						{
							$UpdateArr 						= 	array();

							$UpdateArr['field']		 		= 	array();
							$UpdateArr['field']['sort'] 	= 	$PowerBB->_POST[$name];

							$UpdateArr['where'] 			=	array('id',$SecList[$x]['id']);

							$update = $PowerBB->core->Update($UpdateArr,'section');

							if ($update)
							{
								$cache = $PowerBB->section->UpdateSectionsCache(array('parent'=>$SecList[$x]['parent']));
							}

							$s[$SecList[$x]['id']] = ($update) ? 'true' : 'false';
						}

						$x += 1;

						$cache = $PowerBB->section->UpdateSectionsCache(array('parent'=>$SecList[$x]['parent']));

						    $section = $SecList[$x]['id'];
						    $subject_nm = $PowerBB->DB->sql_num_rows($PowerBB->DB->sql_query("SELECT  *   FROM " . $PowerBB->table['subject'] . " WHERE section = '$section' "));

				            // The number of section's subjects number
				     		$UpdateArr 					= 	array();
				     		$UpdateArr['field']			=	array();

				     		$UpdateArr['field']['subject_num'] 	= 	$subject_nm;
				     		$UpdateArr['where']					= 	array('id',$section);

				     		$UpdateSubjectNumber = $PowerBB->core->Update($UpdateArr,'section');
				            $PowerBB->cache->UpdateSubjectNumber(array('subject_num'	=>	$subject_nm));

				            // The number of section's subjects number
				            $section = $SecList[$x]['id'];
						    $reply_num = $PowerBB->DB->sql_num_rows($PowerBB->DB->sql_query("SELECT  *   FROM " . $PowerBB->table['reply'] . " WHERE section = '$section' "));

				     		$UpdateArr 					= 	array();
				     		$UpdateArr['field']			=	array();

				     		$UpdateArr['field']['reply_num'] 	= 	$reply_num;
				     		$UpdateArr['where']					= 	array('id',$section);

				     		$UpdateReplyNumber = $PowerBB->core->Update($UpdateArr,'section');
				     		$PowerBB->cache->UpdateReplyNumber(array('reply_num'	=>	$reply_num));
				   }


		     		$UpdateArr 					= 	array();
     				$UpdateArr['field']			=	array();

     				$UpdateArr['field']['subject_num'] 	= 	$FromSubjectNumber + $ToSubjectNumber;
     				$UpdateArr['field']['reply_num'] 	= 	$FromReplyNumber + $ToReplyNumber;
     				$UpdateArr['where']					= 	array('id',$PowerBB->_POST['section']);

		     		$update = $PowerBB->core->Update($UpdateArr,'section');

		     		// update Reply to Section
		     		$get_last_subject = $PowerBB->DB->sql_query("SELECT id FROM " . $PowerBB->table['subject'] . " WHERE section = '$section' ORDER BY id ASC");
                      while ($getsubject_row = $PowerBB->DB->sql_fetch_array($get_last_subject));
                    {
	     		        $ReplyUpdateArr 					= 	array();
		   				$ReplyUpdateArr['field']			=	array();

		   				$ReplyUpdateArr['field']['section'] 	= 	$PowerBB->_POST['section'];
		   				$ReplyUpdateArr['where']					= 	array('subject_id',$getSection_row['id']);

		     		    $updateReply = $PowerBB->core->Update($ReplyUpdateArr,'reply');
                    }


						$cache = $PowerBB->section->UpdateSectionsCache(array('parent'=>$PowerBB->_CONF['template']['Inf']['parent']));
			            $cache = $PowerBB->section->UpdateSectionsCache(array('id'=>$PowerBB->_CONF['template']['Inf']['id']));
						$cache = $PowerBB->section->UpdateSectionsCache(array('id'=>$PowerBB->_CONF['template']['Inf']['parent']));

							$PowerBB->functions->msg($PowerBB->_CONF['template']['_CONF']['lang']['Information_has_been_updated_successfully']);

							$DelArr 						= 	array();
							$DelArr['where']				=	array();
							$DelArr['where'][0]				=	array();
							$DelArr['where'][0]['name']		=	'section_id';
							$DelArr['where'][0]['oper']		=	'=';
							$DelArr['where'][0]['value']	=	$PowerBB->_CONF['template']['Inf']['id'];

							$del = $PowerBB->core->Deleted($DelArr,'sectiongroup');

							if ($del)
							{
								$PowerBB->functions->msg($PowerBB->_CONF['template']['_CONF']['lang']['groups_have_been_deleted_successfully']);
								$PowerBB->functions->redirect('index.php?page=forums&amp;control=1&amp;main=1');
							}

				}
			}
		}
		elseif ($PowerBB->_POST['choose'] == 'del')
		{

         $section_parent = $PowerBB->_CONF['template']['Inf']['id'];
         $get_section_parent = $PowerBB->DB->sql_query("SELECT  *   FROM " . $PowerBB->table['section'] . " WHERE parent = " . $section_parent . " ");

	       while ($Inf_row = $PowerBB->DB->sql_fetch_array($get_section_parent))
	      {
				$DelSubjectsArr 						= 	array();
				$DelSubjectsArr['where']				=	array();
				$DelSubjectsArr['where'][0]				=	array();
				$DelSubjectsArr['where'][0]['name']		=	'section';
				$DelSubjectsArr['where'][0]['oper']		=	'=';
				$DelSubjectsArr['where'][0]['value']	=	$Inf_row['id'];

				$DelSubjects = $PowerBB->core->Deleted($DelSubjectsArr,'subject');

				$DelReplysArr 						= 	array();
				$DelReplysArr['where']				=	array();
				$DelReplysArr['where'][0]				=	array();
				$DelReplysArr['where'][0]['name']		=	'section';
				$DelReplysArr['where'][0]['oper']		=	'=';
				$DelReplysArr['where'][0]['value']	=	$Inf_row['id'];

				$DelReplys = $PowerBB->core->Deleted($DelReplysArr,'reply');

				$DelSectionsArr 			= 	array();
				$DelSectionsArr['where'] 	= 	array('id',$Inf_row['id']);

				$DelSections= $PowerBB->core->Deleted($DelSectionsArr,'section');
	      }

				$DelReplyArr 						= 	array();
				$DelReplyArr['where']				=	array();
				$DelReplyArr['where'][0]				=	array();
				$DelReplyArr['where'][0]['name']		=	'section';
				$DelReplyArr['where'][0]['oper']		=	'=';
				$DelReplyArr['where'][0]['value']	=	$PowerBB->_CONF['template']['Inf']['id'];

				$DelReply = $PowerBB->core->Deleted($DelReplyArr,'reply');

				$DelArr 						= 	array();
				$DelArr['where']				=	array();
				$DelArr['where'][0]				=	array();
				$DelArr['where'][0]['name']		=	'section';
				$DelArr['where'][0]['oper']		=	'=';
				$DelArr['where'][0]['value']	=	$PowerBB->_CONF['template']['Inf']['id'];

				$del = $PowerBB->core->Deleted($DelArr,'subject');



					$PowerBB->functions->msg($PowerBB->_CONF['template']['_CONF']['lang']['Topic_has_been_deleted_successfully']);

							$DelArr 			= 	array();
							$DelArr['where'] 	= 	array('id',$PowerBB->_CONF['template']['Inf']['id']);

							$del = $PowerBB->core->Deleted($DelArr,'section');
							$PowerBB->functions->msg($PowerBB->_CONF['template']['_CONF']['lang']['Forum_has_been_deleted_successfully']);


					$cache = $PowerBB->section->UpdateSectionsCache(array('parent'=>$PowerBB->_CONF['template']['Inf']['parent']));

			            $cache = $PowerBB->section->UpdateSectionsCache(array('id'=>$PowerBB->_CONF['template']['Inf']['id']));
						$cache = $PowerBB->section->UpdateSectionsCache(array('id'=>$PowerBB->_CONF['template']['Inf']['parent']));


					        $SecArr 					= 	array();
							$SecArr['get_from']			=	'db';
							$SecArr['proc'] 			= 	array();
							$SecArr['proc']['*'] 		= 	array('method'=>'clean','param'=>'html');
							$SecArr['order']			=	array();
							$SecArr['order']['field']	=	'sort';
							$SecArr['order']['type']	=	'ASC';

							$SecArr['where']				=	array();
							$SecArr['where'][0]				=	array();
							$SecArr['where'][0]['name']		=	'parent';
							$SecArr['where'][0]['oper']		=	'<>';
							$SecArr['where'][0]['value']	=	'0';

							$SecList = $PowerBB->core->GetList($SecArr,'section');

							$x = 0;
							$y = sizeof($SecList);
							$s = array();

							while ($x < $y)
							{
							$name = 'order-' . $SecList[$x]['id'];

							if ($SecList[$x]['order'] != $PowerBB->_POST[$name])
							{
							$UpdateArr 						= 	array();

							$UpdateArr['field']		 		= 	array();
							$UpdateArr['field']['sort'] 	= 	$PowerBB->_POST[$name];

							$UpdateArr['where'] 			=	array('id',$SecList[$x]['id']);

							$update = $PowerBB->core->Update($UpdateArr,'section');

							if ($update)
							{
							$cache = $PowerBB->section->UpdateSectionsCache(array('parent'=>$SecList[$x]['parent']));
							}

							$s[$SecList[$x]['id']] = ($update) ? 'true' : 'false';
							}

							$x += 1;
							}

							if (in_array('false',$s))
							{
							$PowerBB->functions->error($PowerBB->_CONF['template']['_CONF']['lang']['did_not_succeed_the_process']);
							}
							$PowerBB->functions->msg($PowerBB->_CONF['template']['_CONF']['lang']['Information_has_been_updated_successfully']);

							$DelArr 						= 	array();
							$DelArr['where']				=	array();
							$DelArr['where'][0]				=	array();
							$DelArr['where'][0]['name']		=	'section_id';
							$DelArr['where'][0]['oper']		=	'=';
							$DelArr['where'][0]['value']	=	$PowerBB->_CONF['template']['Inf']['id'];

							$del = $PowerBB->core->Deleted($DelArr,'sectiongroup');


							$SecArr 					= 	array();
							$SecArr['get_from']			=	'db';
							$SecArr['proc'] 			= 	array();
							$SecArr['proc']['*'] 		= 	array('method'=>'clean','param'=>'html');
							$SecArr['order']			=	array();
							$SecArr['order']['field']	=	'sort';
							$SecArr['order']['type']	=	'ASC';

							$SecArr['where']				=	array();
							$SecArr['where'][0]				=	array();
							$SecArr['where'][0]['name']		=	'parent';
							$SecArr['where'][0]['oper']		=	'<>';
							$SecArr['where'][0]['value']	=	'0';

							$SecList = $PowerBB->core->GetList($SecArr,'section');

							$x = 0;
							$y = sizeof($SecList);
							$s = array();

							while ($x < $y)
							{
							$name = 'order-' . $SecList[$x]['id'];

							if ($SecList[$x]['order'] != $PowerBB->_POST[$name])
							{
							$UpdateArr 						= 	array();

							$UpdateArr['field']		 		= 	array();
							$UpdateArr['field']['sort'] 	= 	$PowerBB->_POST[$name];

							$UpdateArr['where'] 			=	array('id',$SecList[$x]['id']);

							$update = $PowerBB->core->Update($UpdateArr,'section');


							$cache = $PowerBB->section->UpdateSectionsCache(array('parent'=>$SecList[$x]['parent']));


							$s[$SecList[$x]['id']] = ($update) ? 'true' : 'false';
							}

							$x += 1;
							}

							if (in_array('false',$s))
							{
							$PowerBB->functions->error($PowerBB->_CONF['template']['_CONF']['lang']['did_not_succeed_the_process']);
							}

							$cache = $PowerBB->section->UpdateSectionsCache(array('parent'=>$PowerBB->_GET['parent']));
							$cache = $PowerBB->section->UpdateSectionsCache(array('id'=>$PowerBB->_GET['parent']));
							$cache = $PowerBB->section->UpdateSectionsCache(array('id'=>$PowerBB->_GET['id']));

							$PowerBB->functions->msg($PowerBB->_CONF['template']['_CONF']['lang']['groups_have_been_deleted_successfully']);
							$PowerBB->functions->redirect('index.php?page=forums&amp;control=1&amp;main=1');




		}
		else
		{
			$PowerBB->functions->error($PowerBB->_CONF['template']['_CONF']['lang']['Choose_incorrectl']);
		}
	}

	function _ChangeSort()
	{
		global $PowerBB;

 		$SecArr 					= 	array();
		$SecArr['get_from']			=	'db';
		$SecArr['proc'] 			= 	array();
		$SecArr['proc']['*'] 		= 	array('method'=>'clean','param'=>'html');
		$SecArr['order']			=	array();
		$SecArr['order']['field']	=	'sort';
		$SecArr['order']['type']	=	'ASC';

		$SecArr['where']				=	array();
		$SecArr['where'][0]				=	array();
		$SecArr['where'][0]['name']		=	'parent';
		$SecArr['where'][0]['oper']		=	'<>';
		$SecArr['where'][0]['value']	=	'0';

		$SecList = $PowerBB->core->GetList($SecArr,'section');

		$x = 0;
		$y = sizeof($SecList);
		$s = array();

		while ($x < $y)
		{
			$name = 'order-' . $SecList[$x]['id'];

			if ($SecList[$x]['order'] != $PowerBB->_POST[$name])
			{
				$UpdateArr 						= 	array();

				$UpdateArr['field']		 		= 	array();
				$UpdateArr['field']['sort'] 	= 	$PowerBB->_POST[$name];

				$UpdateArr['where'] 			=	array('id',$SecList[$x]['id']);

				$update = $PowerBB->core->Update($UpdateArr,'section');

				if ($update)
				{
					$cache = $PowerBB->section->UpdateSectionsCache(array('parent'=>$SecList[$x]['parent']));
				}

				$s[$SecList[$x]['id']] = ($update) ? 'true' : 'false';
			}

			$x += 1;
		}

		if (in_array('false',$s))
		{
			$PowerBB->functions->error($PowerBB->_CONF['template']['_CONF']['lang']['did_not_succeed_the_process']);
		}
		else
		{
			$PowerBB->functions->msg($PowerBB->_CONF['template']['_CONF']['lang']['Forum_has_been_updated_successfully']);
			$PowerBB->functions->redirect('index.php?page=forums&amp;control=1&amp;main=1');
		}
	}

	function _GroupControlMain()
	{
		global $PowerBB;

		$PowerBB->_CONF['template']['Inf'] = false;

		$this->check_by_id($PowerBB->_CONF['template']['Inf']);

		$SecGroupArr 						= 	array();
		$SecGroupArr['where'] 				= 	array();

		$SecGroupArr['where'][0]			=	array();
		$SecGroupArr['where'][0]['name'] 	= 	'section_id';
		$SecGroupArr['where'][0]['oper']	=	'=';
		$SecGroupArr['where'][0]['value'] 	= 	$PowerBB->_CONF['template']['Inf']['id'];

		$SecGroupArr['where'][1]			=	array();
		$SecGroupArr['where'][1]['con']		=	'AND';
		$SecGroupArr['where'][1]['name']	=	'main_section';
		$SecGroupArr['where'][1]['oper']	=	'<>';
		$SecGroupArr['where'][1]['value']	=	'1';

		$PowerBB->_CONF['template']['while']['SecGroupList'] = $PowerBB->core->GetList($SecGroupArr,'sectiongroup');

		$PowerBB->template->display('forums_groups_control_main');
	}

	function _GroupControlStart()
	{
		global $PowerBB;

		$PowerBB->_CONF['template']['Inf'] = false;

		$this->check_by_id($PowerBB->_CONF['template']['Inf']);

		$PowerBB->functions->CleanVariable($PowerBB->_GET['group_id'],'intval');

		$success 	= 	array();
		$fail		=	array();
		$size		=	sizeof($PowerBB->_POST['groups']);

		foreach ($PowerBB->_POST['groups'] as $id => $val)
		{
			$UpdateArr 				= 	array();
			$UpdateArr['field']		=	array();

			$UpdateArr['field']['view_section'] 		= 	$val['view_section'];
			$UpdateArr['field']['view_subject'] 		= 	$val['view_subject'];
			$UpdateArr['field']['download_attach'] 		= 	$val['download_attach'];
			$UpdateArr['field']['write_subject'] 		= 	$val['write_subject'];
			$UpdateArr['field']['write_reply'] 			= 	$val['write_reply'];
			$UpdateArr['field']['upload_attach'] 		= 	$val['upload_attach'];
			$UpdateArr['field']['edit_own_subject'] 	= 	$val['edit_own_subject'];
			$UpdateArr['field']['edit_own_reply'] 		= 	$val['edit_own_reply'];
			$UpdateArr['field']['del_own_subject'] 		= 	$val['del_own_subject'];
			$UpdateArr['field']['del_own_reply'] 		= 	$val['del_own_reply'];
			$UpdateArr['field']['write_poll'] 			= 	$val['write_poll'];
			$UpdateArr['field']['no_posts'] 			= 	$val['no_posts'];
			$UpdateArr['field']['vote_poll'] 			= 	$val['vote_poll'];
			$UpdateArr['where'][0] 						= 	array('name'=>'group_id','oper'=>'=','value'=>$id);
			$UpdateArr['where'][1] 						= 	array('con'=>'AND','name'=>'section_id','oper'=>'=','value'=>$PowerBB->_CONF['template']['Inf']['id']);

			$update = $PowerBB->core->Update($UpdateArr,'sectiongroup');


			unset($UpdateArr);

			if ($update)
			{
				$success[] = $id;
			}
			else
			{
				$fail[] = $id;
			}

			unset($update);
		}

		$success_size 	= 	sizeof($success);
		$fail_size		=	sizeof($fail);

		if ($success_size == $size)
		{
			$PowerBB->functions->msg($PowerBB->_CONF['template']['_CONF']['lang']['Forum_has_been_updated_successfully']);

			$UpdateArr 			= 	array();
			$UpdateArr['id'] 	= 	$PowerBB->_CONF['template']['Inf']['id'];

			$cache = $PowerBB->group->UpdateSectionGroupCache($UpdateArr);


				    $SecArr 					= 	array();
					$SecArr['get_from']			=	'db';
					$SecArr['proc'] 			= 	array();
					$SecArr['proc']['*'] 		= 	array('method'=>'clean','param'=>'html');
					$SecArr['order']			=	array();
					$SecArr['order']['field']	=	'sort';
					$SecArr['order']['type']	=	'ASC';

					$SecArr['where']				=	array();
					$SecArr['where'][0]				=	array();
					$SecArr['where'][0]['name']		=	'parent';
					$SecArr['where'][0]['oper']		=	'<>';
					$SecArr['where'][0]['value']	=	'0';

					$SecList = $PowerBB->core->GetList($SecArr,'section');

					$x = 0;
					$y = sizeof($SecList);
					$s = array();

					while ($x < $y)
					{
						$name = 'order-' . $SecList[$x]['id'];

						if ($SecList[$x]['order'] != $PowerBB->_POST[$name])
						{
							$UpdateArr 						= 	array();

							$UpdateArr['field']		 		= 	array();
							$UpdateArr['field']['sort'] 	= 	$PowerBB->_POST[$name];

							$UpdateArr['where'] 			=	array('id',$SecList[$x]['id']);

							$update = $PowerBB->core->Update($UpdateArr,'section');

							if ($update)
							{
								$cache = $PowerBB->section->UpdateSectionsCache(array('parent'=>$SecList[$x]['parent']));
							}

							$s[$SecList[$x]['id']] = ($update) ? 'true' : 'false';
						}

						$x += 1;
					}

					if (in_array('false',$s))
					{
						$PowerBB->functions->error($PowerBB->_CONF['template']['_CONF']['lang']['did_not_succeed_the_process']);
					}
				$PowerBB->functions->msg($PowerBB->_CONF['template']['_CONF']['lang']['Updated_information_cached']);

				$UpdateArr 				= 	array();
				$UpdateArr['parent'] 	= 	$PowerBB->_CONF['template']['Inf']['parent'];

				$cache = $PowerBB->section->UpdateSectionsCache($UpdateArr);


					$PowerBB->functions->msg($PowerBB->_CONF['template']['_CONF']['lang']['Was_the_final_step']);
					$PowerBB->functions->redirect('index.php?page=forums&amp;groups=1&amp;control_group=1&amp;index=1&amp;id=' . $PowerBB->_CONF['template']['Inf']['id']);
		}
	}

	function _ForumMain()
	{
		global $PowerBB;

		//////////

		$PowerBB->_CONF['template']['Inf'] = false;

		$this->check_by_id($PowerBB->_CONF['template']['Inf']);

		//////////

        $SecArr 						= 	array();
		$SecArr['get_from']				=	'db';

		$SecArr['proc'] 				= 	array();
		$SecArr['proc']['*'] 			= 	array('method'=>'clean','param'=>'html');

		$SecArr['order']				=	array();
		$SecArr['order']['field']		=	'sort';
		$SecArr['order']['type']		=	'ASC';

		$SecArr['where']				=	array();
		$SecArr['where'][0]['name']		= 	'parent';
		$SecArr['where'][0]['oper']		= 	'=';
		$SecArr['where'][0]['value']	= 	$PowerBB->_CONF['template']['Inf']['id'];

		// Get main sections
		$forums = $PowerBB->core->GetList($SecArr,'section');

		// We will use forums_list to store list of forums which will view in main page
		$PowerBB->_CONF['template']['foreach']['forums_list'] = array();

		// Loop to read the information of main sections
		           foreach ($forums as $forum)
					{
						//////////////////////////

							$forum['is_sub'] 	= 	0;
							$forum['sub']		=	'';

							@include("../cache/forums_cache/forums_cache_".$forum['id'].".php");

                               if (!empty($forums_cache))
	                           {

									$subs = json_decode(base64_decode($forums_cache), true);
	                               foreach ($subs as $sub)
									{
									   if ($forum['id'] == $sub['parent'])
	                                    {

												if (!$forum['is_sub'])
												{
													$forum['is_sub'] = 1;
												}
												 $forum['sub'] .= ('<option value="' .$sub['id'] . '">---- '  . $sub['title'] . '</option>');

										  }

					                         ///////////////

													$forum['is_sub_sub'] 	= 	0;
													$forum['sub_sub']		=	'';

										@include("../cache/forums_cache/forums_cache_".$sub['id'].".php");

		                                   if (!empty($forums_cache))
				                           {

												$subs_sub = json_decode(base64_decode($forums_cache), true);
				                               foreach ($subs_sub as $sub_sub)
												{
												   if ($sub['id'] == $sub_sub['parent'])
				                                    {

																	if (!$forum['is_sub_sub'])
																	{
																		$forum['is_sub_sub'] = 1;
																	}

															 $forum['sub_sub'] .= ('<option value="' .$sub_sub['id'] . '">---- '  . $sub_sub['title'] . '</option>');
													  }
												 }

										   }
									 }
								}


							$PowerBB->_CONF['template']['foreach']['forums_list'][$forum['id'] . '_f'] = $forum;

		             } // end foreach ($forums)
		//////////
		$PowerBB->template->display('forums_forum_main');
	}
}

class _functions
{
	function check_by_id(&$Inf)
	{
		global $PowerBB;

		if (empty($PowerBB->_GET['id']))
		{
			$PowerBB->functions->error($PowerBB->_CONF['template']['_CONF']['lang']['The_request_is_not_valid']);
		}

		$PowerBB->_GET['id'] = $PowerBB->functions->CleanVariable($PowerBB->_GET['id'],'intval');

    	$CatArr = $PowerBB->DB->sql_query("SELECT  *   FROM " . $PowerBB->table['section'] . " WHERE id = ".$PowerBB->_GET['id']." ");
		$Inf = $PowerBB->DB->sql_fetch_array($CatArr);


		if ($Inf == false)
		{
			$PowerBB->functions->error($PowerBB->_CONF['template']['_CONF']['lang']['Section_requested_does_not_exist']);
		}

		$PowerBB->functions->CleanVariable($Inf,'html');
	}
}

?>
