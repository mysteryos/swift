<?php

Namespace Swift;

class BootstrapPresenterPjax extends \Illuminate\Pagination\BootstrapPresenter {

	/**
	 * Get HTML wrapper for a page link.
	 *
	 * @param  string  $url
	 * @param  int  $page
	 * @param  string  $rel
	 * @return string
	 */
	public function getPageLinkWrapper($url, $page, $rel = null)
	{
		$rel = is_null($rel) ? '' : ' rel="'.$rel.'"';

		return '<li><a class="pjax" href="'.$url.'"'.$rel.'>'.$page.'</a></li>';
	}    
}