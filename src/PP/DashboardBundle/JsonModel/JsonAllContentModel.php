<?php

namespace PP\DashboardBundle\JsonModel;

/**
 * Description of jsonUserModel
 *
 * @author Olivier
 */
class JsonAllContentModel {
    
    public function __construct($categories, $tags, $postCategoryUrl, $deleteCategoryUrl, $patchCategoryUrl){
        $this->categories = $categories;
        $this->tags = $tags;
        $this->postCategoryUrl = $postCategoryUrl;
        $this->deleteCategoryUrl = $deleteCategoryUrl;
        $this->patchCategoryUrl = $patchCategoryUrl;
    }
    
    public $categories;
    public $tags;
    public $postCategoryUrl;
    public $deleteCategoryUrl;
    public $patchCategoryUrl;
    
}
