<?php

namespace ExWife\Engine\Web\News\Controller;

use Doctrine\DBAL\Connection;
use ExWife\Engine\Cms\Core\Base\Controller\BaseController;
use ExWife\Engine\Cms\Core\Base\Controller\Traits\ManageControllerTratis;
use ExWife\Engine\Cms\Core\Base\Controller\Traits\WebControllerTratis;
use ExWife\Engine\Cms\Core\Service\UtilsService;
use ExWife\Engine\Cms\File\Service\FileManagerService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;


class NewsController extends BaseController
{
    use WebControllerTratis;

    /**
     * @route("/news")
     * @route("/news/{category}")
     * @param Request $request
     * @return JsonResponse
     * @throws RedirectException
     */
    public function news(Request $request, $category = null)
    {
        $params = $this->getParamsByUrl('/news');

        $fullClass = UtilsService::getFullClassFromName('NewsCategory');
        $params['newsCategories'] = $fullClass::active($this->_connection);
        $params['selectNewsCategory'] = $fullClass::getActiveBySlug($this->_connection, $category);
        $params = array_merge($params, $this->filterNewsResult($request, $params['selectNewsCategory']));
        return $this->render('news/news-list.twig', $params);
    }

    /**
     * @route("/news/article/{slug}")
     * @return Response
     */
    public function newsArticle(Request $request, $slug)
    {
        $params = $this->getParamsByRequest($request);
        return $this->render('news/news-article.twig', $params);
    }

    /**
     * @route("/news/filter/news")
     * @route("/news/filter/news/{category}")
     * @param Request $request
     * @param null $category
     * @return JsonResponse
     */
    public function newsFilter(Request $request, $category = null)
    {
        $fullClass = UtilsService::getFullClassFromName('NewsCategory');
        $params['selectNewsCategory'] = $fullClass::getActiveBySlug($this->_connection, $category);
        $params = array_merge($params, $this->filterNewsResult($request, $params['selectNewsCategory']));
        return new JsonResponse([
            'html' => $this->_environment->render('news/includes/news-results.twig', $params),
            'total' => $params['total'],
        ]);
    }

    /**
     * @param Request $request
     * @param null $category
     * @return array
     */
    protected function filterNewsResult(Request $request, $selectNewsCategory)
    {
        $limit = getenv('NEWS_LISTING_LIMIT') ?: 21;
        $pageNum = $request->get('pageNum') ?: 1;
        $sort = $request->get('sortby') ?: 'm.date';
        $order = 'DESC';

        $whereSql = '';
        $params = [];

        if ($selectNewsCategory) {
            $whereSql .= ($whereSql ? ' AND ' : '') . '(m.categories LIKE ?)';
            $params = array_merge($params, ['%"' . $selectNewsCategory->id . '"%']);
        }

        $fullClass = UtilsService::getFullClassFromName('News');
        $news = $fullClass::active($this->_connection, [
            'whereSql' => $whereSql,
            'params' => $params,
            'page' => $pageNum,
            'limit' => $limit,
            'sort' => $sort,
            'order' => $order,
            'debug' => 0,
        ]);

        $total = $fullClass::active($this->_connection, [
            'whereSql' => $whereSql,
            'params' => $params,
            'count' => 1
        ]);

        $pageTotal = ceil($total['count'] / $limit);

        return [
            'news' => $news,
            'pageNum' => $pageNum,
            'pageTotal' => $pageTotal,
            'total' => $total,
            'sort' => $sort,
            'limit' => $limit,
        ];
    }
}
