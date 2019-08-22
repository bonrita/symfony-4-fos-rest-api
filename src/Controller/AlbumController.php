<?php

namespace App\Controller;

use App\Entity\Album;
use App\Form\AlbumType;
use App\Repository\AlbumRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
Use FOS\RestBundle\Controller\Annotations as Rest;
use phpDocumentor\Reflection\Types\Null_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use function Symfony\Component\Debug\Tests\testHeader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Rest\RouteResource("Album",
 *     pluralize=false
 *     )
 */
class AlbumController extends AbstractFOSRestController implements ClassResourceInterface
{

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var \App\Repository\AlbumRepository
     */
    private $albumRepository;

    /**
     * AlbumController constructor.
     */
    public function __construct(
      EntityManagerInterface $entityManager,

      AlbumRepository $albumRepository
    ) {
        $this->entityManager = $entityManager;
        $this->albumRepository = $albumRepository;
    }

    public function postAction(Request $request)
    {
        $form = $this->createForm(AlbumType::class, new Album());

        $form->submit($request->request->all());

        if (false === $form->isValid()) {
            return $this->view($form);
        }

        $this->entityManager->persist($form->getData());
        $this->entityManager->flush();

        return $this->view(
          [
            'status' => 'ok',
          ],
          Response::HTTP_CREATED
        );

    }

    public function getAction($id)
    {
        return $this->view($this->findAlbumById($id));
    }

    /**
     * @param $id
     *
     * @return Album|null
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function findAlbumById($id)
    {
        $album = $this->albumRepository->find($id);

        if (null === $album) {
            throw new NotFoundHttpException();
        }

        return $album;
    }

    public function cgetAction()
    {
        return $this->view($this->albumRepository->findAll());
    }

    public function putAction(Request $request, $id)
    {
        $existingAlbum = $this->findAlbumById($id);

        $form = $this->createForm(AlbumType::class, $existingAlbum);
        $form->submit($request->request->all());

        if (false === $form->isValid()) {
            return $this->view($form);
        }

        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    public function patchAction(Request $request, string $id)
    {
        $existingAlbum = $this->findAlbumById($id);

        $form = $this->createForm(AlbumType::class, $existingAlbum);

        $form->submit($request->request->all(), false);

        if (false === $form->isValid()) {
            return $this->view($form);
        }

        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    public function deleteAction(string $id)
    {
        $album = $this->findAlbumById($id);
        $this->entityManager->remove($album);
        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

}
