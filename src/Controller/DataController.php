<?php

namespace App\Controller;

use App\Entity\Data;
use App\Repository\DataRepository;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/data')]
#[OA\Tag(name: 'Data (music groups)')]
class DataController extends AbstractController
{
    #[Route('', name: 'api_data_list', methods: ['GET'])]
    #[OA\Get(summary: 'Return all music groups.')]
    public function list(DataRepository $dataRepository): JsonResponse
    {
        return $this->json($dataRepository->findAll(), context: ['groups' => 'getDataList']);
    }

    #[Route('', name: 'api_data_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'You do not have sufficient rights to create a music group.')]
    #[OA\Post(summary: 'Create a new music group.')]
    public function create(
        Request $request,
        DataRepository $dataRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        UrlGeneratorInterface $urlGenerator
    ): JsonResponse {
        /** @var Data $data */
        $data = $serializer->deserialize($request->getContent(), Data::class, 'json');
        $errors = $validator->validate($data);

        if ($errors->count()) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, $errors);
        }

        $dataRepository->add($data, true);

        $location = $urlGenerator->generate(
            'api_data_read',
            ['id' => $data->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->json($data, Response::HTTP_CREATED, ['Location' => $location]);
    }

    #[Route('/{id}', name: 'api_data_read', methods: ['GET'])]
    #[OA\Get(summary: 'Read a music group.')]
    public function read(Data $data): JsonResponse
    {
        return $this->json($data);
    }

    #[Route('/{id}', name: 'api_data_update', methods: ['PATCH'])]
    #[IsGranted('ROLE_ADMIN', message: 'You do not have sufficient rights to update a music group.')]
    #[OA\Patch(summary: 'Update a music group.')]
    public function update(
        Request $request,
        Data $data,
        DataRepository $dataRepository,
        SerializerInterface $serializer
    ): JsonResponse {
        $updatedData = $serializer->deserialize(
            $request->getContent(),
            Data::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $data]
        );

        $dataRepository->add($updatedData, true);

        return $this->json($updatedData);
    }

    #[Route('/{id}', name: 'api_data_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'You do not have sufficient rights to delete a music group.')]
    #[OA\Delete(summary: 'Delete a music group.')]
    public function delete(Data $data, DataRepository $dataRepository): JsonResponse
    {
        $dataRepository->remove($data, true);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
