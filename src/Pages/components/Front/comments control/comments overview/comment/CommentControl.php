<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 25.02.2016
 */

namespace Comments\Components\Front;

use App\Components\BaseControl;
use blitzik\FlashMessages\FlashMessage;
use Comments\Facades\CommentFacade;
use Nette\Security\User;
use Comments\Comment;
use Pages\Exceptions\Runtime\ActionFailedException;

class CommentControl extends BaseControl
{
    /** @var array */
    public $onSuccessCommentRemoval;

    /** @var CommentFacade */
    private $commentFacade;

    /** @var User */
    private $user;


    /** @var Comment */
    private $comment;


    public function __construct(
        Comment $comment,
        CommentFacade $commentFacade,
        User $user
    ) {
        $this->comment = $comment;
        $this->commentFacade = $commentFacade;
        $this->user = $user;
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/comment.latte');

        $template->comment = $this->comment;

        $template->render();
    }


    public function handleSilence()
    {
        $this->userPermissionCheck();

        $this->comment->silence();
        $this->commentFacade->update($this->comment);

        $this->refresh('Text komentáře byl úspěšně potlačen', FlashMessage::SUCCESS, ['comment-text', 'comment-suppress']);
    }


    public function handleRelease()
    {
        $this->userPermissionCheck();

        $this->comment->release();
        $this->commentFacade->update($this->comment);

        $this->refresh('Text komentáře byl úspěšně zobrazen', FlashMessage::SUCCESS, ['comment-text', 'comment-suppress']);
    }


    public function handleRemove()
    {
        $this->userPermissionCheck();

        try {
            $this->commentFacade->remove($this->comment);
        } catch (ActionFailedException $e) {
            $this->refresh('Při odstraňování komentáře došlo k chybě', FlashMessage::ERROR);
        }

        $this->onSuccessCommentRemoval($this->comment);
        $this->redirect('this#comments');
    }


    private function userPermissionCheck()
    {
        if (!$this->user->isLoggedIn()) {
            $this->flashMessage('K provedení akce nemáte dostatečná oprávnění', FlashMessage::WARNING);
            $this->redirect('this#' . $this->comment->getId());
        }
    }


    private function refresh($message, $type, array $snippets = null)
    {
        if ($this->presenter->isAjax()) {
            if ($snippets === null) { return; }
            if (empty($snippets)) { $this->redrawControl(); return; }

            foreach ($snippets as $snippet) {
                $this->redrawControl($snippet);
            }
        } else {
            $this->flashMessage($message, $type);
            $this->redirect('this#comment-' . $this->comment->getId());
        }
    }
}


interface ICommentControlFactory
{
    /**
     * @param Comment $comment
     * @return CommentControl
     */
    public function create(Comment $comment);
}