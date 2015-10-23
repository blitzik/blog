<?php

namespace Pages\Facades;

use App\Exceptions\Runtime\ArticleTitleAlreadyExistsException;
use App\Exceptions\Runtime\UrlAlreadyExistsException;
use Doctrine\DBAL\DBALException;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Kdyby\Monolog\Logger;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Object;
use Nette\Utils\Arrays;
use Nette\Utils\Strings;
use Pages\Article;
use Pages\Query\ArticleQuery;
use Url\Url;

class PageFacade extends Object
{
    /** @var EntityManager  */
    private $em;

    /** @var Logger  */
    private $logger;

    /** @var  EntityRepository */
    private $articleRepository;

    /** @var  Cache */
    private $cache;

    public function __construct(
        EntityManager $entityManager,
        IStorage $storage,
        Logger $logger
    ) {
        $this->em = $entityManager;
        $this->logger = $logger->channel('pages');
        $this->cache = new Cache($storage, 'articles');

        $this->articleRepository = $this->em->getRepository(Article::class);
    }

    /**
     * @param Article $article
     * @return array ['article' => Article, 'url' => Url]
     * @throws ArticleTitleAlreadyExistsException
     * @throws UrlAlreadyExistsException
     */
    public function save(Article $article)
    {
        $article->publish();

        try {
            $this->em->beginTransaction();

            $article = $this->em->safePersist($article);
            if ($article === false) {
                throw new ArticleTitleAlreadyExistsException;
            }

            $articleUrl = $this->establishArticleUrl($article);
            $articleUrl = $this->em->safePersist($articleUrl);
            if ($articleUrl === false) {
                throw new UrlAlreadyExistsException;
            }

            $this->em->commit();
            return ['article' => $article, 'url' => $articleUrl];

        } catch (DBALException $e) {
            $this->em->rollback();
            $this->em->close();

            $this->logger->addError('Article saving error');
        }
    }

    /**
     * @param Article $article
     * @return Url
     */
    private function establishArticleUrl(Article $article)
    {
        $url = new Url;
        $url->setUrlPath(Strings::webalize($article->title));
        $url->setDestination(Article::PRESENTER, Article::PRESENTER_ACTION);
        $url->setInternalId($article->getId());

        return $url;
    }

    /**
     * @param ArticleQuery $query
     * @return array|\Kdyby\Doctrine\ResultSet
     */
    public function fetchArticles(ArticleQuery $query)
    {
        return $this->articleRepository->fetch($query);
    }

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @return mixed|null|object
     */
    public function findOneBy(array $criteria, array $orderBy = null)
    {
        return $this->articleRepository->findOneBy($criteria, $orderBy);
    }

    /**
     * @param $articleId
     * @return array|null
     */
    public function getArticle($articleId)
    {
        $article = $this->cache->load(Article::getCacheKey($articleId),
                                      function (& $dependencies) use ($articleId) {
            $article = $this->getBaseArticleDql()
                            ->where('a.id = :id')
                            ->setParameter('id', $articleId)
                            ->getQuery()
                            ->getArrayResult();

            if (empty($article)) {
                return null;
            }

            $article = $article[0];
            $dependencies = [Cache::TAGS => Article::getCacheKey($articleId)];
            return $article;
        });

        return $article;
    }

    /**
     * @return array
     */
    public function findPublishedArticles()
    {
        $articles = $this->getBaseArticleDql()
                         ->where('a.isPublished = true')
                         ->orderBy('a.publishedAt', 'DESC')
                         ->getQuery()
                         ->getArrayResult();
        return Arrays::associate($articles, 'id');
    }

    /**
     * @return \Kdyby\Doctrine\QueryBuilder
     */
    private function getBaseArticleDql()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('a, partial aa.{id, username, firstName, lastName}, t')
           ->from(Article::class, 'a')
           ->innerJoin('a.author', 'aa')
           ->leftJoin('a.tags', 't');

        return $qb;
    }
}