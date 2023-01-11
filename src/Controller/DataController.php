<?php

namespace App\Controller;

use App\Entity\Data;
use App\Repository\DataRepository;
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
class DataController extends AbstractController
{
    #[Route('', name: 'api_data_list', methods: ['GET'])]
    public function list(DataRepository $dataRepository): JsonResponse
    {
        return $this->json($dataRepository->findAll());
    }

    #[Route('', name: 'api_data_create', methods: ['POST'])]
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
    public function read(Data $data): JsonResponse
    {
        return $this->json($data);
    }

    #[Route('/{id}', name: 'api_data_update', methods: ['PATCH'])]
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
    public function delete(Data $data, DataRepository $dataRepository): JsonResponse
    {
        $dataRepository->remove($data, true);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
