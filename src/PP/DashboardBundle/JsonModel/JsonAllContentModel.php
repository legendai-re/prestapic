<?php

namespace PP\DashboardBundle\JsonModel;

/**
 * Description of jsonUserModel
 *
 * @author Olivier
 */
class JsonAllContentModel {
    
    public function __construct($categories, $tags, $reportReasons, $postCategoryUrl, $deleteCategoryUrl, $patchCategoryUrl, $deleteTagUrl, $postReportReasonUrl, $deleteReportReasonUrl){
        $this->categories = $categories;
        $this->tags = $tags;
        $this->reportReasons = $reportReasons;
        $this->postCategoryUrl = $postCategoryUrl;
        $this->deleteCategoryUrl = $deleteCategoryUrl;
        $this->patchCategoryUrl = $patchCategoryUrl;        
        $this->deleteTagUrl = $deleteTagUrl;
        $this->postReportReasonUrl = $postReportReasonUrl;
        $this->deleteReportReasonUrl = $deleteReportReasonUrl;
    }
    
    public $categories;
    public $tags;
    public $reportReasons;
    public $postCategoryUrl;
    public $deleteCategoryUrl;
    public $patchCategoryUrl;
    public $deleteTagUrl;
    public $postReportReasonUrl;
    public $deleteReportReasonUrl;
}
