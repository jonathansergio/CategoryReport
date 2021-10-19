<?php

namespace Js\CategoryReport\Model;

use Exception;
use Magento\Catalog\Api\CategoryListInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\Data\CategorySearchResultsInterface;

class AllCategory
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CategoryListInterface
     */
    private $categoryList;

    public function __construct(
        CategoryListInterface $categoryList,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->categoryList = $categoryList;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Fetch all Category list
     *
     * @return CategorySearchResultsInterface
     */
    public function getAllSystemCategory()
    {
        $categoryList = [];
        try {
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $categoryList = $this->categoryList->getList($searchCriteria);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }

        return $categoryList;
    }
}
