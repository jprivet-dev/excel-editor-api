<?php

namespace App\Controller;

use App\Entity\Data;
use App\Repository\DataRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/data')]
class DataController extends AbstractController
{
    #[Route('', name: 'api_data_read_all', methods: ['GET'])]
    public function read(DataRepository $dataRepository): JsonResponse
    {
        return $this->json($dataRepository->findAll());
    }

    #[Route('', name: 'api_data_create', methods: ['POST'])]
    public function create(
        Request $request,
        DataRepository $dataRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        /** @var Data $data */
        $data = $serializer->deserialize($request->getContent(), Data::class, 'json');
        $errors = $validator->validate($data);

        if ($errors->count() > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $dataRepository->add($data, true);

        return $this->json($data, Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_data_read', methods: ['GET'])]
    public function readItem(Data $data): JsonResponse
    {
        return $this->json($data);
    }

    #[Route('/{id}', name: 'api_data_update', methods: ['PUT'])]
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

        // TODO: vérifier que le nom du groupe mis à jour n'est pas déjà pris.

        $dataRepository->add($updatedData, true);

        return $this->json($updatedData);
    }

    #[Route('/{id}', name: 'api_data_delete', methods: ['DELETE'])]
    public function delete(Data $data, DataRepository $dataRepository): JsonResponse
    {
        $dataRepository->remove($data, true);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
