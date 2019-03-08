<?php
/**
 * Created by PhpStorm.
 * User: mkornevich
 * Date: 27.02.2019
 * Time: 20:40
 */

namespace App;


class InputRow
{
    public $article;
    public $searchQuery;
    public $imgLimit = 4;
    public $status = 0;
    public $searchCount = 0;
    public $searchService = "none";
    public $searchQuery1 = 0;


    public function __construct($data)
    {
        $this->setRowData($data);
    }

    public function setRowData($data)
    {
        $this->article = $data[0];
        $this->searchQuery = $data[1];
        $this->imgLimit = $data[2] ?? $this->imgLimit;
        $this->status = $data[3] ?? $this->status;
        $this->searchCount = $data[4] ?? $this->searchCount;
        $this->searchService = $data[5] ?? $this->searchService;
        $this->searchQuery1 = $data[6] ?? $this->searchQuery1;

    }

    public function getRowData()
    {
        $data = [];
        $data[0] = $this->article;
        $data[1] = $this->searchQuery;
        $data[2] = $this->imgLimit;
        $data[3] = $this->status;
        $data[4] = $this->searchCount;
        $data[5] = $this->searchService;
        $data[6] = $this->searchQuery1;
        return $data;
    }
}