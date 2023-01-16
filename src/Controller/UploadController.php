<?php

namespace App\Controller;

use App\Form\FileUploadType;
use App\Service\DataImportService;
use App\Service\FileUploadService;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/upload')]
#[OA\Tag(name: 'Upload (Excel files)')]
class UploadController extends AbstractController
{
    #[Route('', name: 'api_upload', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'You do not have sufficient rights to upload a file.')]
    #[OA\Post(summary: 'Upload the music groups from an excel file.')]
    public function upload(
        Request $request,
        FileUploadService $fileUpload,
        DataImportService $importService,
    ): JsonResponse {
        $requestFile = $request->files->get('file');

        $form = $this->createForm(FileUploadType::class, options: ['csrf_protection' => false]);

        $form->submit([
            'file' => $requestFile,
        ]);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form->get('file')->getData();

            if (!$uploadedFile) {
                throw new InvalidArgumentException("'file' field does not empty.");
            }

            $file = $fileUpload->save($uploadedFile);

            // TODO: trouver la meilleure approche pour fusionner les stats dans la réponse json.
            $stats = $importService->import($file);

            return $this->json($stats, Response::HTTP_CREATED);
        }

        throw new InvalidArgumentException($form->getErrors(true));
    }
}
