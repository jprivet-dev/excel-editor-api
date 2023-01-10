<?php

namespace App\Controller;

use App\Entity\Data;
use App\Repository\DataRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/data')]
class ApiDataController extends AbstractController
{
    #[Route('', name: 'api_data_read', methods: ['GET'])]
    public function read(DataRepository $dataRepository): JsonResponse
    {
        return $this->json($dataRepository->findAll());
    }

    #[Route('', name: 'api_data_create', methods: ['POST'])]
    public function create(
        Request $request,
        DataRepository $dataRepository,
        SerializerInterface $serializer
    ): JsonResponse {
        /** @var Data $data */
        $data = $serializer->deserialize($request->getContent(), Data::class, 'json');

        $alreadyExists = $dataRepository->findBy([
            'nomDuGroupe' => $data->getNomDuGroupe(),
        ]);

        if (count($alreadyExists) > 0) {
            throw new InvalidArgumentException(sprintf('Le groupe "%s" existe déjà.', $data->getNomDuGroupe()));
        }

        $dataRepository->add($data, true);

        return $this->json($data, Response::HTTP_CREATED);
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

        // TODO: vérifier que le nom du groupe mis à jour n'existe n'est pas déjà pris.

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
