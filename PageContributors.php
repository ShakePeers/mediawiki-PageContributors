<?php

if( !defined( 'MEDIAWIKI' ) ) {
	echo( "This file is an extension to the MediaWiki software and cannot be used standalone.\n" );
	die( 1 );
}

$wgExtensionCredits['variable'][] = array(
	'path' => __FILE__,
	'name' => 'PageContributors',
	'author' => 'Corentin Rabet',
	'url' => 'http://mediawiki.org/wiki/Extension:PageContributors',
);

$dir = dirname(__FILE__) . '/';
$wgExtensionMessagesFiles['PageContributors'] = $dir . 'PageContributors.i18n.magic.php';
$wgHooks['ParserGetVariableValueSwitch'][] = "wfPageContributorsSetup";

function wfPageContributorsSetup (&$parser , &$cache , &$magicWordId , &$ret) {
	if ( 'PageContributors' == $magicWordId ) {
		global $wgTitle,$wgOut,$wgRequest;
		global $list;

		$NS = $wgTitle->getNamespace();
		$action = $wgRequest->getVal('action');

		if (($NS >= 0) and ($NS <= 7000) and ($action != 'edit')) {
			$dbr =& wfGetDB( DB_SLAVE );
			$page_id = $wgTitle->getArticleID(); $list= '';

			$res = $dbr->select(
			'revision',
			array('distinct rev_user_text'),
			array("rev_page = $page_id","rev_user >= 1"),
			__METHOD__,
			array('ORDER BY' => 'rev_user_text ASC',));

			if( $res && $dbr->numRows( $res ) > 0 ) {
				while( $row = $dbr->fetchObject( $res ) ) {
					$deletedUser = preg_match("/ZDelete/",$row->rev_user_text);
					if (!$deletedUser) {
			 			$list .= "[[User:".$row->rev_user_text."|".$row->rev_user_text."]], ";
					}
				}
			}

			$dbr->freeResult( $res );
			$list = preg_replace('/\,\s*$/','',$list);
		}
		$ret = $list;
	}
	return true;
}

$wgHooks['MagicWordwgVariableIDs'][] = "wfPageContributorsDeclareVariable";

function wfPageContributorsDeclareVariable ( &$customVariableIds ) {
	$customVariableIds[] = 'PageContributors';
	return true;
}
