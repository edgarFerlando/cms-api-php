<?php namespace App;

use Landish\Pagination\Pagination as BasePagination;

class Pagination extends BasePagination {

	protected $paginationWrapper = '<ul class="pagination">%s %s %s</ul>';
    protected $availablePageWrapper = '<li><a href="%s">%s</a></li>';
    protected $activePageWrapper = '<li class="current"><a href="">%s</a></li>';//'<li class="current"><span>%s</span></li>';
    protected $disabledPageWrapper = '<li class="disabled"><span>%s</span></li>';//'<li class="unavailable"><a href="">%s</a></li>';
    protected $previousButtonText = '<a href=""><i class="fa fa-caret-left"></i></a>';//'<span><i class="fa fa-caret-left"></i></span>';
    protected $nextButtonText = '<a href=""><i class="fa fa-caret-right"></i></a>';//'<span><i class="fa fa-caret-right"></i></span>';

}