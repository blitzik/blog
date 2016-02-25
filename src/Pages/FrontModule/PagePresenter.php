<?php

namespace Pages\FrontModule\Presenters;

use Comments\Components\Front\ICommentsOverviewControlFactory;
use Comments\Components\Front\ICommentFormControlFactory;
use Pages\Components\Front\IPagesOverviewControlFactory;
use Pages\Components\Front\IPageControlFactory;
use App\FrontModule\Presenters\BasePresenter;
use Nette\Application\BadRequestException;
use Pages\Facades\PageFacade;
use Pages\Page;
use Pages\Query\PageQuery;

class PagePresenter extends BasePresenter
{
    /**
     * @var ICommentsOverviewControlFactory
     * @inject
     */
    public $commentsOverviewFactory;

    /**
     * @var IPagesOverviewControlFactory
     * @inject
     */
    public $pagesOverviewFactory;

    /**
     * @var ICommentFormControlFactory
     * @inject
     */
    public $commentsFormFactory;

    /**
     * @var IPageControlFactory
     * @inject
     */
    public $pageFactory;

    /**
     * @var PageFacade
     * @inject
     */
    public $pageFacade;

    /**
     * @var Page
     */
    private $page;


    /*
     * -----------------------------------------
     * ----- ARTICLES OVERVIEW BY CATEGORY -----
     * -----------------------------------------
     */


    public function actionDefault()
    {
    }


    public function renderDefault()
    {
    }


    protected function createComponentPagesOverview()
    {
        $comp = $this->pagesOverviewFactory->create();
        if (isset($this->options['articles_per_page'])) {
            $comp->setPagesPerPage($this->options['articles_per_page']);
        }

        /*$comp->onPaginate[] = function (Paginator $paginator) {
            $paginator->setPage($this->getParameter('p'));
        };*/

        return $comp;
    }


    /*
     * ------------------------------
     * ----- PARTICULAR ARTICLE -----
     * ------------------------------
     */


    public function actionShow($internal_id)
    {
        $page = $this->pageFacade
                     ->fetchPage(
                         (new PageQuery())
                          ->withTags()
                          ->byPageId($internal_id)
                          ->onlyPublished()
                     );

        if ($page === null) {
            throw new BadRequestException;
        }

        $this['pageTitle']->setPageTitle($this->options->blog_title)
                          ->joinTitleText(' - ' . $page->title);

        $this->page = $page;
    }


    public function renderShow($internal_id)
    {
        $this->template->page = $this->page;
    }


    protected function createComponentPage()
    {
        $comp = $this-> pageFactory->create($this->page);
        return $comp;
    }


    protected function createComponentCommentsOverview()
    {
        $comp = $this->commentsOverviewFactory->create($this->page);
        return $comp;
    }


    protected function createComponentCommentsForm()
    {
        $comp = $this->commentsFormFactory
                     ->create($this->page);

        return $comp;
    }
}