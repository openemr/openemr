<?php
// File: $Id$ $Name$
// ----------------------------------------------------------------------
// POST-NUKE Content Management System
// Copyright (C) 2001 by the Post-Nuke Development Team.
// http://www.postnuke.com/
// ----------------------------------------------------------------------
// Based on:
// Thatware - http://thatware.org/
// PHP-NUKE Web Portal System - http://phpnuke.org/
// ----------------------------------------------------------------------
// LICENSE
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------


$prefix = $pnconfig['prefix'];

$pntable = array();

$autonews = $prefix . '_autonews';
$pntable['autonews'] = $autonews;
$pntable['autonews_column'] = array ('anid'      => $autonews . '.pn_anid',
                                     'catid'     => $autonews . '.pn_catid',
                                     'aid'       => $autonews . '.pn_aid',
                                     'title'     => $autonews . '.pn_title',
                                     'time'      => $autonews . '.pn_time',
                                     'hometext'  => $autonews . '.pn_hometext',
                                     'bodytext'  => $autonews . '.pn_bodytext',
                                     'topic'     => $autonews . '.pn_topic',
                                     'informant' => $autonews . '.pn_informant',
                                     'notes'     => $autonews . '.pn_notes',
                                     'ihome'     => $autonews . '.pn_ihome',
                                     'alanguage' => $autonews . '.pn_language',
                                     'language'  => $autonews . '.pn_language',
                                     'withcomm'  => $autonews . '.pn_withcomm');

$banner = $prefix . '_banner';
$pntable['banner'] = $banner;
$pntable['banner_column'] = array ('bid'      => $banner . '.pn_bid',
                                   'cid'      => $banner . '.pn_cid',
                                   'type'     => $banner . '.pn_type',
                                   'imptotal' => $banner . '.pn_imptotal',
                                   'impmade'  => $banner . '.pn_impmade',
                                   'clicks'   => $banner . '.pn_clicks',
                                   'imageurl' => $banner . '.pn_imageurl',
                                   'clickurl' => $banner . '.pn_clickurl',
                                   'date'     => $banner . '.pn_date');

$bannerclient = $prefix . '_bannerclient';
$pntable['bannerclient'] = $bannerclient;
$pntable['bannerclient_column'] = array ('cid'       => $bannerclient . '.pn_cid',
                                         'name'      => $bannerclient . '.pn_name',
                                         'contact'   => $bannerclient . '.pn_contact',
                                         'email'     => $bannerclient . '.pn_email',
                                         'login'     => $bannerclient . '.pn_login',
                                         'passwd'    => $bannerclient . '.pn_passwd',
                                         'extrainfo' => $bannerclient . '.pn_extrainfo');

$bannerfinish = $prefix . '_bannerfinish';
$pntable['bannerfinish'] = $bannerfinish;
$pntable['bannerfinish_column'] = array ('bid'         => $bannerfinish . '.pn_bid',
                                         'cid'         => $bannerfinish . '.pn_cid',
                                         'impressions' => $bannerfinish . '.pn_impressions',
                                         'clicks'      => $bannerfinish . '.pn_clicks',
                                         'datestart'   => $bannerfinish . '.pn_datestart',
                                         'dateend'     => $bannerfinish . '.pn_dateend');

$blocks = $prefix . '_blocks';
$pntable['blocks'] = $blocks;
$pntable['blocks_column'] = array ('bid'         => $blocks . '.pn_bid',
                                   'bkey'        => $blocks . '.pn_bkey',
                                   'title'       => $blocks . '.pn_title',
                                   'content'     => $blocks . '.pn_content',
                                   'url'         => $blocks . '.pn_url',
                                   'mid'         => $blocks . '.pn_mid',
                                   'position'    => $blocks . '.pn_position',
                                   'weight'      => $blocks . '.pn_weight',
                                   'active'      => $blocks . '.pn_active',
                                   'refresh'     => $blocks . '.pn_refresh',
                                   'last_update' => $blocks . '.pn_last_update',
                                   'blanguage'   => $blocks . '.pn_language',
                                   'language'    => $blocks . '.pn_language');

$blocks_buttons = $prefix . '_blocks_buttons';
$pntable['blocks_buttons'] = $blocks_buttons;
$pntable['blocks_buttons_column'] = array ('id'     => $blocks_buttons . '.pn_id',
                                           'bid'    => $blocks_buttons . '.pn_bid',
                                           'title'  => $blocks_buttons . '.pn_title',
                                           'url'    => $blocks_buttons . '.pn_url',
                                           'images' => $blocks_buttons . '.pn_images');

$comments = $prefix . '_comments';
$pntable['comments'] = $comments;
$pntable['comments_column'] = array ('tid'       => $comments . '.pn_tid',
                                     'pid'       => $comments . '.pn_pid',
                                     'sid'       => $comments . '.pn_sid',
                                     'date'      => $comments . '.pn_date',
                                     'name'      => $comments . '.pn_name',
                                     'email'     => $comments . '.pn_email',
                                     'url'       => $comments . '.pn_url',
                                     'host_name' => $comments . '.pn_host_name',
                                     'subject'   => $comments . '.pn_subject',
                                     'comment'   => $comments . '.pn_comment',
                                     'score'     => $comments . '.pn_score',
                                     'reason'    => $comments . '.pn_reason');

$counter = $prefix . '_counter';
$pntable['counter'] = $counter;
$pntable['counter_column'] = array ('type'  => $counter . '.pn_type',
                                    'var'   => $counter . '.pn_var',
                                    'count' => $counter . '.pn_count');

$downloads_categories = $prefix . '_downloads_categories';
$pntable['downloads_categories'] = $downloads_categories;
$pntable['downloads_categories_column'] = array ('cid'          => $downloads_categories . '.pn_cid',
                                                 'title'        => $downloads_categories . '.pn_title',
                                                 'cdescription' => $downloads_categories . '.pn_description');

$downloads_downloads = $prefix . '_downloads_downloads';
$pntable['downloads_downloads'] = $downloads_downloads;
$pntable['downloads_downloads_column'] = array ('lid'                   => $downloads_downloads . '.pn_lid',
                                                'cid'                   => $downloads_downloads . '.pn_cid',
                                                'sid'                   => $downloads_downloads . '.pn_sid',
                                                'title'                 => $downloads_downloads . '.pn_title',
                                                'url'                   => $downloads_downloads . '.pn_url',
                                                'description'           => $downloads_downloads . '.pn_description',
                                                'date'                  => $downloads_downloads . '.pn_date',
                                                'name'                  => $downloads_downloads . '.pn_name',
                                                'email'                 => $downloads_downloads . '.pn_email',
                                                'hits'                  => $downloads_downloads . '.pn_hits',
                                                'submitter'             => $downloads_downloads . '.pn_submitter',
                                                'downloadratingsummary' => $downloads_downloads . '.pn_ratingsummary',
                                                'totalvotes'            => $downloads_downloads . '.pn_totalvotes',
                                                'totalcomments'         => $downloads_downloads . '.pn_totalcomments',
                                                'filesize'              => $downloads_downloads . '.pn_filesize',
                                                'version'               => $downloads_downloads . '.pn_version',
                                                'homepage'              => $downloads_downloads . '.pn_homepage');

$downloads_editorials = $prefix . '_downloads_editorials';
$pntable['downloads_editorials'] = $downloads_editorials;
$pntable['downloads_editorials_column'] = array ('downloadid'         => $downloads_editorials . '.pn_id',
                                                 'adminid'            => $downloads_editorials . '.pn_adminid',
                                                 'editorialtimestamp' => $downloads_editorials . '.pn_timestamp',
                                                 'editorialtext'      => $downloads_editorials . '.pn_text',
                                                 'editorialtitle'     => $downloads_editorials . '.pn_title');

$downloads_modrequest = $prefix . '_downloads_modrequest';
$pntable['downloads_modrequest'] = $downloads_modrequest;
$pntable['downloads_modrequest_column'] = array ('requestid'       => $downloads_modrequest . '.pn_requestid',
                                                 'lid'             => $downloads_modrequest . '.pn_lid',
                                                 'cid'             => $downloads_modrequest . '.pn_cid',
                                                 'sid'             => $downloads_modrequest . '.pn_sid',
                                                 'title'           => $downloads_modrequest . '.pn_title',
                                                 'url'             => $downloads_modrequest . '.pn_url',
                                                 'description'     => $downloads_modrequest . '.pn_description',
                                                 'modifysubmitter' => $downloads_modrequest . '.pn_modifysubmitter',
                                                 'brokendownload'  => $downloads_modrequest . '.pn_brokendownload',
                                                 'name'            => $downloads_modrequest . '.pn_name',
                                                 'email'           => $downloads_modrequest . '.pn_email',
                                                 'filesize'        => $downloads_modrequest . '.pn_filesize',
                                                 'version'         => $downloads_modrequest . '.pn_version',
                                                 'homepage'        => $downloads_modrequest . '.pn_homepage');

$downloads_newdownload = $prefix . '_downloads_newdownload';
$pntable['downloads_newdownload'] = $downloads_newdownload;
$pntable['downloads_newdownload_column'] = array ('lid'         => $downloads_newdownload . '.pn_lid',
                                                  'cid'         => $downloads_newdownload . '.pn_cid',
                                                  'sid'         => $downloads_newdownload . '.pn_sid',
                                                  'title'       => $downloads_newdownload . '.pn_title',
                                                  'url'         => $downloads_newdownload . '.pn_url',
                                                  'description' => $downloads_newdownload . '.pn_description',
                                                  'name'        => $downloads_newdownload . '.pn_name',
                                                  'email'       => $downloads_newdownload . '.pn_email',
                                                  'submitter'   => $downloads_newdownload . '.pn_submitter',
                                                  'filesize'    => $downloads_newdownload . '.pn_filesize',
                                                  'version'     => $downloads_newdownload . '.pn_version',
                                                  'homepage'    => $downloads_newdownload . '.pn_homepage');

$downloads_subcategories = $prefix . '_downloads_subcategories';
$pntable['downloads_subcategories'] = $downloads_subcategories;
$pntable['downloads_subcategories_column'] = array ('sid'   => $downloads_subcategories . '.pn_sid',
                                                    'cid'   => $downloads_subcategories . '.pn_cid',
                                                    'title' => $downloads_subcategories . '.pn_title');

$downloads_votedata = $prefix . '_downloads_votedata';
$pntable['downloads_votedata'] = $downloads_votedata;
$pntable['downloads_votedata_column'] = array ('ratingdbid'      => $downloads_votedata . '.pn_id',
                                               'ratinglid'       => $downloads_votedata . '.pn_lid',
                                               'ratinguser'      => $downloads_votedata . '.pn_user',
                                               'rating'          => $downloads_votedata . '.pn_rating',
                                               'ratinghostname'  => $downloads_votedata . '.pn_hostname',
                                               'ratingcomments'  => $downloads_votedata . '.pn_comments',
                                               'ratingtimestamp' => $downloads_votedata . '.pn_timestamp');

$ephem = $prefix . '_ephem';
$pntable['ephem'] = $ephem;
$pntable['ephem_column'] = array ('eid'       => $ephem . '.pn_eid',
                                  'did'       => $ephem . '.pn_did',
                                  'mid'       => $ephem . '.pn_mid',
                                  'yid'       => $ephem . '.pn_yid',
                                  'content'   => $ephem . '.pn_content',
                                  'elanguage' => $ephem . '.pn_language',
                                  'language'  => $ephem . '.pn_language');

$faqanswer = $prefix . '_faqanswer';
$pntable['faqanswer'] = $faqanswer;
$pntable['faqanswer_column'] = array ('id'          => $faqanswer . '.pn_id',
                                      'id_cat'      => $faqanswer . '.pn_id_cat',
                                      'question'    => $faqanswer . '.pn_question',
                                      'answer'      => $faqanswer . '.pn_answer',
                                      'submittedby' => $faqanswer . '.pn_submittedby');

$faqcategories = $prefix . '_faqcategories';
$pntable['faqcategories'] = $faqcategories;
$pntable['faqcategories_column'] = array ('id_cat'     => $faqcategories . '.pn_id_cat',
                                          'categories' => $faqcategories . '.pn_categories',
                                          'flanguage'  => $faqcategories . '.pn_language',
                                          'language'   => $faqcategories . '.pn_language',
                                          'parent_id'  => $faqcategories . '.pn_parent_id');

$group_membership = $prefix . '_group_membership';
$pntable['group_membership'] = $group_membership;
$pntable['group_membership_column'] = array ('gid' => $group_membership . '.pn_gid',
                                             'uid' => $group_membership . '.pn_uid');

$group_perms = $prefix . '_group_perms';
$pntable['group_perms'] = $group_perms;
$pntable['group_perms_column'] = array ('pid'       => $group_perms . '.pn_pid',
                                        'gid'       => $group_perms . '.pn_gid',
                                        'sequence'  => $group_perms . '.pn_sequence',
                                        'realm'     => $group_perms . '.pn_realm',
                                        'component' => $group_perms . '.pn_component',
                                        'instance'  => $group_perms . '.pn_instance',
                                        'level'     => $group_perms . '.pn_level',
                                        'bond'      => $group_perms . '.pn_bond');

$groups = $prefix . '_groups';
$pntable['groups'] = $groups;
$pntable['groups_column'] = array ('gid'  => $groups . '.pn_gid',
                                   'name' => $groups . '.pn_name');

$headlines = $prefix . '_headlines';
$pntable['headlines'] = $headlines;
$pntable['headlines_column'] = array ('id'        => $headlines . '.pn_id',
                                      'sitename'  => $headlines . '.pn_sitename',
                                      'rssuser'   => $headlines . '.pn_rssuser',
                                      'rsspasswd' => $headlines . '.pn_rsspasswd',
                                      'use_proxy' => $headlines . '.pn_use_proxy',
                                      'rssurl'    => $headlines . '.pn_rssurl',
                                      'maxrows'   => $headlines . '.pn_maxrows',
                                      'siteurl'   => $headlines . '.pn_siteurl',
                                      'options'   => $headlines . '.pn_options');

$hooks = $prefix . '_hooks';
$pntable['hooks'] = $hooks;
$pntable['hooks_column'] = array ('id'        => $hooks . '.pn_id',
                                  'object'    => $hooks . '.pn_object',
                                  'action'    => $hooks . '.pn_action',
                                  'smodule'   => $hooks . '.pn_smodule',
                                  'stype'     => $hooks . '.pn_stype',
                                  'tarea'     => $hooks . '.pn_tarea',
                                  'tmodule'   => $hooks . '.pn_tmodule',
                                  'ttype'     => $hooks . '.pn_ttype',
                                  'tfunc'     => $hooks . '.pn_tfunc');

$languages_constant = $prefix.'_languages_constant';
$pntable['languages_constant']    = $languages_constant;
$pntable['languages_constant_column'] = array ('constant' => $languages_constant . '.pn_constant',
                                               'file'     => $languages_constant . '.pn_file');

$languages_file = $prefix.'_languages_file';
$pntable['languages_file']     = $languages_file;
$pntable['languages_file_column'] = array ('target' => $languages_file . '.pn_target',
                                           'source' => $languages_file . '.pn_source');
                                               
$languages_translation = $prefix.'_languages_translation';
$pntable['languages_translation'] = $languages_translation;
$pntable['languages_translation_column'] = array ('language'    => $languages_translation . '.pn_language',
                                                  'constant'    => $languages_translation . '.pn_constant',
                                                  'translation' => $languages_translation . '.pn_translation',
                                                  'level'       => $languages_translation . '.pn_level');

$links_categories = $prefix . '_links_categories';
$pntable['links_categories'] = $links_categories;
$pntable['links_categories_column'] = array ('cat_id'       => $links_categories . '.pn_cat_id',
                                             'parent_id'    => $links_categories . '.pn_parent_id',
                                             'title'        => $links_categories . '.pn_title',
                                             'cdescription' => $links_categories . '.pn_description');

$links_editorials = $prefix . '_links_editorials';
$pntable['links_editorials'] = $links_editorials;
$pntable['links_editorials_column'] = array ('linkid'             => $links_editorials . '.pn_linkid',
                                             'adminid'            => $links_editorials . '.pn_adminid',
                                             'editorialtimestamp' => $links_editorials . '.pn_timestamp',
                                             'editorialtext'      => $links_editorials . '.pn_text',
                                             'editorialtitle'     => $links_editorials . '.pn_title');

$links_links = $prefix . '_links_links';
$pntable['links_links'] = $links_links;
$pntable['links_links_column'] = array ('lid'               => $links_links . '.pn_lid',
                                        'cat_id'            => $links_links . '.pn_cat_id',
                                        'title'             => $links_links . '.pn_title',
                                        'url'               => $links_links . '.pn_url',
                                        'description'       => $links_links . '.pn_description',
                                        'date'              => $links_links . '.pn_date',
                                        'name'              => $links_links . '.pn_name',
                                        'email'             => $links_links . '.pn_email',
                                        'hits'              => $links_links . '.pn_hits',
                                        'submitter'         => $links_links . '.pn_submitter',
                                        'linkratingsummary' => $links_links . '.pn_ratingsummary',
                                        'totalvotes'        => $links_links . '.pn_totalvotes',
                                        'totalcomments'     => $links_links . '.pn_totalcomments');

$links_modrequest = $prefix . '_links_modrequest';
$pntable['links_modrequest'] = $links_modrequest;
$pntable['links_modrequest_column'] = array ('requestid'       => $links_modrequest . '.pn_requestid',
                                             'lid'             => $links_modrequest . '.pn_lid',
                                             'cat_id'          => $links_modrequest . '.pn_cat_id',
                                             'sid'             => $links_modrequest . '.pn_sid',
                                             'title'           => $links_modrequest . '.pn_title',
                                             'url'             => $links_modrequest . '.pn_url',
                                             'description'     => $links_modrequest . '.pn_description',
                                             'modifysubmitter' => $links_modrequest . '.pn_modifysubmitter',
                                             'brokenlink'      => $links_modrequest . '.pn_brokenlink');

$links_newlink = $prefix . '_links_newlink';
$pntable['links_newlink'] = $links_newlink;
$pntable['links_newlink_column'] = array ('lid'         => $links_newlink . '.pn_lid',
                                          'cat_id'      => $links_newlink . '.pn_cat_id',
                                          'title'       => $links_newlink . '.pn_title',
                                          'url'         => $links_newlink . '.pn_url',
                                          'description' => $links_newlink . '.pn_description',
                                          'name'        => $links_newlink . '.pn_name',
                                          'email'       => $links_newlink . '.pn_email',
                                          'submitter'   => $links_newlink . '.pn_submitter');

$links_votedata = $prefix . '_links_votedata';
$pntable['links_votedata'] = $links_votedata;
$pntable['links_votedata_column'] = array ('ratingdbid'      => $links_votedata . '.pn_id',
                                           'ratinglid'       => $links_votedata . '.pn_lid',
                                           'ratinguser'      => $links_votedata . '.pn_user',
                                           'rating'          => $links_votedata . '.pn_rating',
                                           'ratinghostname'  => $links_votedata . '.pn_hostname',
                                           'ratingcomments'  => $links_votedata . '.pn_comments',
                                           'ratingtimestamp' => $links_votedata . '.pn_timestamp');

$message = $prefix . '_message';
$pntable['message'] = $message;
$pntable['message_column'] = array ('mid'         => $message . '.pn_mid',
                                    'title'       => $message . '.pn_title',
                                    'content'     => $message . '.pn_content',
                                    'date'        => $message . '.pn_date',
                                    'expire'      => $message . '.pn_expire',
                                    'active'      => $message . '.pn_active',
                                    'view'        => $message . '.pn_view',
                                    'mlanguage'   => $message . '.pn_language',
                                    'language'    => $message . '.pn_language');

$module_vars = $prefix . '_module_vars';
$pntable['module_vars'] = $module_vars;
$pntable['module_vars_column'] = array ('id'      => $module_vars . '.pn_id',
                                        'modname' => $module_vars . '.pn_modname',
                                        'name'    => $module_vars . '.pn_name',
                                        'value'   => $module_vars . '.pn_value');

$modules = $prefix . '_modules';
$pntable['modules'] = $modules;
$pntable['modules_column'] = array ('id'            => $modules . '.pn_id',
                                    'name'          => $modules . '.pn_name',
                                    'type'          => $modules . '.pn_type',
                                    'displayname'   => $modules . '.pn_displayname',
                                    'description'   => $modules . '.pn_description',
                                    'regid'         => $modules . '.pn_regid',
                                    'directory'     => $modules . '.pn_directory',
                                    'version'       => $modules . '.pn_version',
                                    'admin_capable' => $modules . '.pn_admin_capable',
                                    'user_capable'  => $modules . '.pn_user_capable',
                                    'state'         => $modules . '.pn_state');

$poll_check = $prefix . '_poll_check';
$pntable['poll_check'] = $poll_check;
$pntable['poll_check_column'] = array ('ip'   => $poll_check . '.pn_ip',
                                       'time' => $poll_check . '.pn_time');

$poll_data = $prefix . '_poll_data';
$pntable['poll_data'] = $poll_data;
$pntable['poll_data_column'] = array ('pollid'      => $poll_data . '.pn_pollid',
                                      'optiontext'  => $poll_data . '.pn_optiontext',
                                      'optioncount' => $poll_data . '.pn_optioncount',
                                      'voteid'      => $poll_data . '.pn_voteid');

$poll_desc = $prefix . '_poll_desc';
$pntable['poll_desc'] = $poll_desc;
$pntable['poll_desc_column'] = array ('pollid'    => $poll_desc . '.pn_pollid',
                                      'polltitle' => $poll_desc . '.pn_title',
                                      'timestamp' => $poll_desc . '.pn_timestamp',
                                      'voters'    => $poll_desc . '.pn_voters',
                                      'planguage' => $poll_desc . '.pn_language',
                                      'language'  => $poll_desc . '.pn_language');

$pollcomments = $prefix . '_pollcomments';
$pntable['pollcomments'] = $pollcomments;
$pntable['pollcomments_column'] = array ('tid'       => $pollcomments . '.pn_tid',
                                         'pid'       => $pollcomments . '.pn_pid',
                                         'pollid'    => $pollcomments . '.pn_pollid',
                                         'date'      => $pollcomments . '.pn_date',
                                         'name'      => $pollcomments . '.pn_name',
                                         'email'     => $pollcomments . '.pn_email',
                                         'url'       => $pollcomments . '.pn_url',
                                         'host_name' => $pollcomments . '.pn_host_name',
                                         'subject'   => $pollcomments . '.pn_subject',
                                         'comment'   => $pollcomments . '.pn_comment',
                                         'score'     => $pollcomments . '.pn_score',
                                         'reason'    => $pollcomments . '.pn_reason');

$priv_msgs = $prefix . '_priv_msgs';
$pntable['priv_msgs'] = $priv_msgs;
$pntable['priv_msgs_column'] = array ('msg_id'      => $priv_msgs . '.pn_msg_id',
                                      'msg_image'   => $priv_msgs . '.pn_msg_image',
                                      'subject'     => $priv_msgs . '.pn_subject',
                                      'from_userid' => $priv_msgs . '.pn_from_userid',
                                      'to_userid'   => $priv_msgs . '.pn_to_userid',
                                      'msg_time'    => $priv_msgs . '.pn_msg_time',
                                      'msg_text'    => $priv_msgs . '.pn_msg_text',
                                      'read_msg'    => $priv_msgs . '.pn_read_msg');

$queue = $prefix . '_queue';
$pntable['queue'] = $queue;
$pntable['queue_column'] = array ('qid'       => $queue . '.pn_qid',
                                  'uid'       => $queue . '.pn_uid',
                                  'arcd'      => $queue . '.pn_arcd',
                                  'uname'     => $queue . '.pn_uname',
                                  'subject'   => $queue . '.pn_subject',
                                  'story'     => $queue . '.pn_story',
                                  'timestamp' => $queue . '.pn_timestamp',
                                  'topic'     => $queue . '.pn_topic',
                                  'alanguage' => $queue . '.pn_language',
                                  'language'  => $queue . '.pn_language',
                                  'bodytext'  => $queue . '.pn_bodytext');

$realms = $prefix . '_realms';
$pntable['realms'] = $realms;
$pntable['realms_column'] = array ('rid'  => $realms . '.pn_rid',
                                   'name' => $realms . '.pn_name');

$referer = $prefix . '_referer';
$pntable['referer'] = $referer;
$pntable['referer_column'] = array ('rid'       => $referer . '.pn_rid',
                                    'url'       => $referer . '.pn_url',
                                    'frequency' => $referer . '.pn_frequency');

$related = $prefix . '_related';
$pntable['related'] = $related;
$pntable['related_column'] = array ('rid'  => $related . '.pn_rid',
                                    'tid'  => $related . '.pn_tid',
                                    'name' => $related . '.pn_name',
                                    'url'  => $related . '.pn_url');

$reviews = $prefix . '_reviews';
$pntable['reviews'] = $reviews;
$pntable['reviews_column'] = array ('id'        => $reviews . '.pn_id',
                                    'date'      => $reviews . '.pn_date',
                                    'title'     => $reviews . '.pn_title',
                                    'text'      => $reviews . '.pn_text',
                                    'reviewer'  => $reviews . '.pn_reviewer',
                                    'email'     => $reviews . '.pn_email',
                                    'score'     => $reviews . '.pn_score',
                                    'cover'     => $reviews . '.pn_cover',
                                    'url'       => $reviews . '.pn_url',
                                    'url_title' => $reviews . '.pn_url_title',
                                    'hits'      => $reviews . '.pn_hits',
                                    'rlanguage' => $reviews . '.pn_language',
                                    'language'  => $reviews . '.pn_language');

$reviews_add = $prefix . '_reviews_add';
$pntable['reviews_add'] = $reviews_add;
$pntable['reviews_add_column'] = array ('id'        => $reviews_add . '.pn_id',
                                        'date'      => $reviews_add . '.pn_date',
                                        'title'     => $reviews_add . '.pn_title',
                                        'text'      => $reviews_add . '.pn_text',
                                        'reviewer'  => $reviews_add . '.pn_reviewer',
                                        'email'     => $reviews_add . '.pn_email',
                                        'score'     => $reviews_add . '.pn_score',
                                        'url'       => $reviews_add . '.pn_url',
                                        'url_title' => $reviews_add . '.pn_url_title',
                                        'rlanguage' => $reviews_add . '.pn_language',
                                        'language'  => $reviews_add . '.pn_language');

$reviews_comments = $prefix . '_reviews_comments';
$pntable['reviews_comments'] = $reviews_comments;
$pntable['reviews_comments_column'] = array ('cid'      => $reviews_comments . '.pn_cid',
                                             'rid'      => $reviews_comments . '.pn_rid',
                                             'userid'   => $reviews_comments . '.pn_userid',
                                             'date'     => $reviews_comments . '.pn_date',
                                             'comments' => $reviews_comments . '.pn_comments',
                                             'score'    => $reviews_comments . '.pn_score');

$reviews_main = $prefix . '_reviews_main';
$pntable['reviews_main'] = $reviews_main;
$pntable['reviews_main_column'] = array ('title'      => $reviews_main . '.pn_title',
                                        'description' => $reviews_main . '.pn_description');

$seccont = $prefix . '_seccont';
$pntable['seccont'] = $seccont;
$pntable['seccont_column'] = array ('artid'        => $seccont . '.pn_artid',
                                    'secid'        => $seccont . '.pn_secid',
                                    'title'        => $seccont . '.pn_title',
                                    'content'      => $seccont . '.pn_content',
                                    'counter'      => $seccont . '.pn_counter',
                                    'slanguage'    => $seccont . '.pn_language',
                                    'language'     => $seccont . '.pn_language');

$sections = $prefix . '_sections';
$pntable['sections'] = $sections;
$pntable['sections_column'] = array ('secid'   => $sections . '.pn_secid',
                                     'secname' => $sections . '.pn_secname',
                                     'image'   => $sections . '.pn_image');

$session_info = $prefix . '_session_info';
$pntable['session_info'] = $session_info;
$pntable['session_info_column'] = array ('sessid'    => $session_info . '.pn_sessid',
                                         'ipaddr'    => $session_info . '.pn_ipaddr',
                                         'firstused' => $session_info . '.pn_firstused',
                                         'lastused'  => $session_info . '.pn_lastused',
                                         'uid'       => $session_info . '.pn_uid',
                                         'vars'      => $session_info . '.pn_vars');

$stats_date = $prefix . '_stats_date';
$pntable['stats_date'] = $stats_date;
$pntable['stats_date_column'] = array ('date'    => $stats_date . '.pn_date',
                                       'hits'    => $stats_date . '.pn_hits');

$stats_hour = $prefix . '_stats_hour';
$pntable['stats_hour'] = $stats_hour;
$pntable['stats_hour_column'] = array ('hour'    => $stats_hour . '.pn_hour',
                                       'hits'    => $stats_hour . '.pn_hits');

$stats_month = $prefix . '_stats_month';
$pntable['stats_month'] = $stats_month;
$pntable['stats_month_column'] = array ('month'  => $stats_month . '.pn_month',
                                        'hits'   => $stats_month . '.pn_hits');

$stats_week = $prefix . '_stats_week';
$pntable['stats_week'] = $stats_week;
$pntable['stats_week_column'] = array ('weekday' => $stats_week . '.pn_weekday',
                                       'hits'    => $stats_week . '.pn_hits');

$stories = $prefix . '_stories';
$pntable['stories'] = $stories;
$pntable['stories_column'] = array ('sid'           => $stories . '.pn_sid',
                                    'cid'           => $stories . '.pn_catid',
                                    'catid'         => $stories . '.pn_catid',  // for back compat
                                    'aid'           => $stories . '.pn_aid',
                                    'title'         => $stories . '.pn_title',
                                    'time'          => $stories . '.pn_time',
                                    'hometext'      => $stories . '.pn_hometext',
                                    'bodytext'      => $stories . '.pn_bodytext',
                                    'comments'      => $stories . '.pn_comments',
                                    'counter'       => $stories . '.pn_counter',
                                    'topic'         => $stories . '.pn_topic',
                                    'informant'     => $stories . '.pn_informant',
                                    'notes'         => $stories . '.pn_notes',
                                    'ihome'         => $stories . '.pn_ihome',
                                    'themeoverride' => $stories . '.pn_themeoverride',
                                    'alanguage'     => $stories . '.pn_language',
                                    'language'      => $stories . '.pn_language',
                                    'withcomm'      => $stories . '.pn_withcomm',
						'format_type'   => $stories . '.pn_format_type');

$stories_cat = $prefix . '_stories_cat';
$pntable['stories_cat'] = $stories_cat;
$pntable['stories_cat_column'] = array ('catid'          => $stories_cat . '.pn_catid',
                                        'title'          => $stories_cat . '.pn_title',
                                        'counter'        => $stories_cat . '.pn_counter',
                                        'themeoverride'  => $stories_cat . '.pn_themeoverride');

$topics = $prefix . '_topics';
$pntable['topics'] = $topics;
$pntable['topics_column'] = array ('tid'        => $topics . '.pn_topicid',
                                   'topicid'    => $topics . '.pn_topicid', // for back compat
                                   'topicname'  => $topics . '.pn_topicname',
                                   'topicimage' => $topics . '.pn_topicimage',
                                   'topictext'  => $topics . '.pn_topictext',
                                   'counter'    => $topics . '.pn_counter');

$user_data = $prefix . '_user_data';
$pntable['user_data'] = $user_data;
$pntable['user_data_column'] = array ('uda_id'       => $user_data . '.pn_uda_id',
                                       'uda_propid'  => $user_data . '.pn_uda_propid',
                                       'uda_uid'     => $user_data . '.pn_uda_uid',
                                       'uda_value'   => $user_data . '.pn_uda_value');

$user_perms = $prefix . '_user_perms';
$pntable['user_perms'] = $user_perms;
$pntable['user_perms_column'] = array ('pid'       => $user_perms . '.pn_pid',
                                       'uid'       => $user_perms . '.pn_uid',
                                       'sequence'  => $user_perms . '.pn_sequence',
                                       'realm'     => $user_perms . '.pn_realm',
                                       'component' => $user_perms . '.pn_component',
                                       'instance'  => $user_perms . '.pn_instance',
                                       'level'     => $user_perms . '.pn_level',
                                       'bond'      => $user_perms . '.pn_bond');

$user_property = $prefix . '_user_property';
$pntable['user_property'] = $user_property;
$pntable['user_property_column'] = array ('prop_id' => $user_property . '.pn_prop_id',
                                  'prop_label'      => $user_property . '.pn_prop_label',
                                  'prop_dtype'      => $user_property . '.pn_prop_dtype',
                                  'prop_length'     => $user_property . '.pn_prop_length',
                                  'prop_weight'     => $user_property . '.pn_prop_weight',
                                  'prop_validation' => $user_property . '.pn_prop_validation'
                                  );

$userblocks = $prefix . '_userblocks';
$pntable['userblocks'] = $userblocks;
$pntable['userblocks_column'] = array ('uid'         => $userblocks . '.pn_uid',
                                       'bid'         => $userblocks . '.pn_bid',
                                       'active'      => $userblocks . '.pn_active',
                                       'lastupdate'  => $userblocks . '.pn_lastupdate');

$users = $prefix . '_users';
$pntable['users'] = $users;
$pntable['users_column'] = array ('uid'             => $users . '.pn_uid',
                                  'name'            => $users . '.pn_name',
                                  'uname'           => $users . '.pn_uname',
                                  'email'           => $users . '.pn_email',
                                  'femail'          => $users . '.pn_femail',
                                  'url'             => $users . '.pn_url',
                                  'user_avatar'     => $users . '.pn_user_avatar',
                                  'user_regdate'    => $users . '.pn_user_regdate',
                                  'user_icq'        => $users . '.pn_user_icq',
                                  'user_occ'        => $users . '.pn_user_occ',
                                  'user_from'       => $users . '.pn_user_from',
                                  'user_intrest'    => $users . '.pn_user_intrest',
                                  'user_sig'        => $users . '.pn_user_sig',
                                  'user_viewemail'  => $users . '.pn_user_viewemail',
                                  'user_theme'      => $users . '.pn_user_theme',
                                  'user_aim'        => $users . '.pn_user_aim',
                                  'user_yim'        => $users . '.pn_user_yim',
                                  'user_msnm'       => $users . '.pn_user_msnm',
                                  'pass'            => $users . '.pn_pass',
                                  'storynum'        => $users . '.pn_storynum',
                                  'umode'           => $users . '.pn_umode',
                                  'uorder'          => $users . '.pn_uorder',
                                  'thold'           => $users . '.pn_thold',
                                  'noscore'         => $users . '.pn_noscore',
                                  'bio'             => $users . '.pn_bio',
                                  'ublockon'        => $users . '.pn_ublockon',
                                  'ublock'          => $users . '.pn_ublock',
                                  'theme'           => $users . '.pn_theme',
                                  'commentmax'      => $users . '.pn_commentmax',
                                  'counter'         => $users . '.pn_counter',
                                  'timezone_offset' => $users . '.pn_timezone_offset');


?>