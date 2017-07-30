<?php

namespace Controller;

use Repository\OfferRepository;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Form\OfferType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;


/**
 * Class HelloController.
 *
 * @package Controller
 */
class OfferController implements ControllerProviderInterface
{
    /**
     * Routing settings.
     *
     * @param \Silex\Application $app Silex application
     *
     * @return \Silex\ControllerCollection Result
     */

    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        $controller->get('/', [$this, 'indexAction'])->bind('offer_index');
        $controller->get('/page/{page}', [$this, 'indexAction'])
            ->value('page', 1)
            ->bind('offer_index_paginated');
        $controller->get('/{id}', [$this, 'viewAction'])
            ->assert('id', '[1-9]\d*')
            ->bind('offer_view');
        $controller->match('/add', [$this, 'addAction'])
            ->method('POST|GET')
            ->bind('offer_add');
        $controller->match('/{id}/edit', [$this, 'editAction'])
            ->method('GET|POST')
            ->assert('id', '[1-9]\d*')
            ->bind('offer_edit');
        $controller->match('/{id}/delete', [$this, 'deleteAction'])
            ->method('GET|POST')
            ->assert('id', '[1-9]\d*')
            ->bind('offer_delete');
        return $controller;
    }


    /**
     * Index action.
     *
     * @param \Silex\Application $app Silex application
     * @param int $page Current page number
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
    public function indexAction(Application $app, $page = 1)
    {
        $offerRepository = new offerRepository($app['db']);

        return $app['twig']->render(
            'offer/index.html.twig',
            ['paginator' => $offerRepository->findAllPaginated($page)]
        );
    }

    /**
     * View action.
     *
     * @param \Silex\Application $app Silex application
     * @param string $id Element Id
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
    public function viewAction(Application $app, $id)
    {
        $offerRepository = new offerRepository($app['db']);
        return $app['twig']->render(
            'offer/view.html.twig',
            ['offer' => $offerRepository->findOneById($id),
                'offerId' => $id
                ]
        );
    }

    public function addAction(Application $app, Request $request)
    {
        $offer = [];

        $form = $app['form.factory']->createBuilder(
            offerType::class,
            $offer
        )->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $offerRepository = new offerRepository($app['db']);
            $offerRepository->save($form->getData());

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_added',
                ]
            );

            return $app->redirect($app['url_generator']->generate('offer_index'), 301);
        }

        return $app['twig']->render(
            'offer/add.html.twig',
            [
                'offer' => $offer,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Edit action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param int                                       $id      Record id
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP Request
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
    public function editAction(Application $app, $id, Request $request)
    {
        $offerRepository = new offerRepository($app['db']);
        $offer = $offerRepository->findOneById($id);

        if (!$offer) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('offer_index'));
        }

        $form = $app['form.factory']->createBuilder(
            offerType::class,
            $offer
        )->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $offerRepository->save($form->getData());

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_edited',
                ]
            );

            return $app->redirect($app['url_generator']->generate('offer_index'), 301);
        }

        return $app['twig']->render(
            'offer/edit.html.twig',
            [
                'offer' => $offer,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param int                                       $id      Record id
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP Request
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
    public function deleteAction(Application $app, $id, Request $request)
    {
        $offerRepository = new offerRepository($app['db']);
        $offer = $offerRepository->findOneById($id);

        if (!$offer) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.offer_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('offer_index'));
        }

        $form = $app['form.factory']->createBuilder(FormType::class, $offer)->add('id', HiddenType::class)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $offerRepository->delete($form->getData());

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_deleted',
                ]
            );

            return $app->redirect(
                $app['url_generator']->generate('offer_index'),
                301
            );
        }

        return $app['twig']->render(
            'offer/delete.html.twig',
            [
                'offer' => $offer,
                'form' => $form->createView(),
            ]
        );
    }
}