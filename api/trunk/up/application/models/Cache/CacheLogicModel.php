<?php

require_once APPPATH . '/models/BaseModel.php';

class ArticleLogicModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model( 'circle/CircleLogicModel', 'circleLogic' );
        $this->load->model( 'article/ArticleLikeModel', 'articleLike' );
        $this->load->model( 'article/ArticleModel', 'article' );
        $this->load->model( 'article/ReplyModel', 'reply' );
        $this->load->model( 'article/TopicModel', 'topic' );
        $this->load->model( 'article/TopicArticleModel', 'topicArticle' );
    }


    /**
     * 刷新文章基础信息
     **/

    public function article( ){


    }

}