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

class DataController extends AbstractController
{
    #[Route('/api/data', name: 'api_data_read', methods: ['GET'])]
    public function read(DataRepository $dataRepository, SerializerInterface $serializer): JsonResponse
    {
        $data = $dataRepository->findAll();
        $json = $this->getJson($serializer, $data);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('/api/data/{id}', name: 'api_data_delete', methods: ['DELETE'])]
    public function delete(Data $data, DataRepository $dataRepository): JsonResponse
    {
        $dataRepository->remove($data, true);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/data', name: 'api_data_create', methods: ['POST'])]
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
        $json = $this->getJson($serializer, $data);

        return new JsonResponse($json, Response::HTTP_CREATED, [], true);
    }

    #[Route('/api/data/{id}', name: 'api_data_update', methods: ['PUT'])]
    public function update(
        Request $request,
        Data $currentData,
        DataRepository $dataRepository,
        SerializerInterface $serializer
    ): JsonResponse {
        $data = $serializer->deserialize(
            $request->getContent(),
            Data::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentData]
        );

        // TODO: vérifier que le nom du groupe mis à jour n'existe n'est pas déjà pris.

        $dataRepository->add($data, true);
        $json = $this->getJson($serializer, $data);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    private function getJson(SerializerInterface $serializer, array|Data $data): string
    {
        // TODO: Tricks. À définir plutôt dans un custom serializer dédier à Data (uniquement pour anneeDebut & anneeSeparation).
        return $serializer->serialize($data, 'json', [
            DateTimeNormalizer::FORMAT_KEY => 'Y',
        ]);
    }
}
