<?php

class sspmod_portal_Portal {
	
	private $pages;
	private $config;
	
	function __construct($pages, $config = NULL) {
		$this->pages = $pages;
		$this->config = $config;
	}
	
	function getTabset($thispage) {
		if (!isset($this->config)) return NULL;
		foreach($this->config AS $set) {
			if (in_array($thispage, $set)) {
				return $set;
			}
		}
		return NULL;
	}
	
	function isPortalized($thispage) {
		foreach($this->config AS $set) {
			if (in_array($thispage, $set)) {
				return TRUE;
			}
		}
		return FALSE;
	}
	
	function getLoginInfo($t, $thispage) {
		$info = array('info' => '', 'template' => $t, 'thispage' => $thispage);
		SimpleSAML_Module::callHooks('portalLoginInfo', $info);
		return $info['info'];
	}
	
	function getMenu($thispage) {
	
		$config = SimpleSAML_Configuration::getInstance();
		$t = new SimpleSAML_XHTML_Template($config, 'sanitycheck:check-tpl.php');
		
		$tabset = $this->getTabset($thispage);
		
		#echo($thispage);
		#echo('<pre>'); print_r($this->pages); exit;
		
		$logininfo = $this->getLoginInfo($t, $thispage);
		#echo $logininfo; exit;
		
		$text = '';
		
		if (!empty($logininfo)) {
			$text .= '<div class="logininfo" style="float: right">' . $logininfo . '</div>';
		}
		
		$text .= '<ul class="ui-tabs-nav">';
		foreach($this->pages AS $pageid => $page) {
			
			if (isset($tabset) && !in_array($pageid, $tabset, TRUE)) continue;
			
			#echo('This page [' . $pageid . '] is part of [' . join(',', $tabset) . ']');
			
			$name = 'uknown';
			if (isset($page['text'])) $name = $page['text'];
			if (isset($page['shorttext'])) $name = $page['shorttext'];
			
			if (!isset($page['href'])) {
				$text .= '<li class="ui-tabs-selected"><a href="#">' . $t->t($name) . '</a></li>';
			} else if($pageid === $thispage ) {
				$text .= '<li class="ui-tabs-selected"><a href="#">' . $t->t($name) . '</a></li>';
			} else {
				$text .= '<li><a href="' . $page['href'] . '">' . $t->t($name) . '</a></li>';
			}
			
		}
		$text .= '</ul>';
		return $text;
	}
	
	
}