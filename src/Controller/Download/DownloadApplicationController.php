<?php

namespace App\Controller\Download;


use ZipArchive;
use App\Entity\Application;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

//#[IsGranted('ROLE_ADMIN')]
class DownloadApplicationController extends AbstractController
{

    private $entityManager;
    private  $application;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->application = $entityManager->getRepository(Application::class);
    }



    #[Route('/download-files/{id}', name: 'api_download_files', methods: ['GET'])]
    public function getDownloadApplication(int $id)
    {
        try {
            $getFile =  $this->application->find($id);

            if (!$getFile) {
                return $this->json('Aucun fichier trouvé pour la candidature', 404);
            }
            $nameResume = basename($getFile->getApplicationResume());
            $nameCoverLetter = basename($getFile->getCoverLetter());

            //dd($nameResume);

            $filenameParts = explode('-', $nameCoverLetter);
            $cleanFilename = $filenameParts[0];

            //dd($filenameParts);

            $filenamePartsCV = explode('-', $nameResume);
            $cleanFilenameCV = $filenamePartsCV[0];

            $filePathCv = $this->getParameter('file_directory') . '/' . basename($nameResume);
            $filePathCoverLetter = realpath($this->getParameter('file_directory') . '/' . basename($nameCoverLetter));

            //dd($filePathCv);

            $zipFile = new ZipArchive();
            $tempFileName = tempnam(sys_get_temp_dir(), 'downloaded_files_');

            if (file_exists($filePathCv)) {
                $zipFile->addFile($filePathCv, 'application_resume.pdf');
            } else {
                throw new \Exception('Le fichier CV n\'existe pas ou n\'est pas accessible.');
            }

            if (file_exists($filePathCoverLetter)) {
                $zipFile->addFile($filePathCoverLetter, 'cover_letter.pdf');
            } else {
                throw new \Exception('La lettre de motivation n\'existe pas ou n\'est pas accessible.');
            }
            
            if ($zipFile->open($tempFileName, \ZipArchive::CREATE) !== true) {
                throw new \Exception('Impossible d\'ouvrir le fichier zip : ' . $zipFile->getStatusString());
            }
            
            if ($zipFile->addFile($filePathCv, 'application_resume.pdf') !== true) {
                throw new \Exception('Impossible d\'ajouter le CV au fichier zip : ' . $zipFile->getStatusString());
            }
            
            if ($zipFile->addFile($filePathCoverLetter, 'cover_letter.pdf') !== true) {
                throw new \Exception('Impossible d\'ajouter la lettre de motivation au fichier zip : ' . $zipFile->getStatusString());
            }
            

            $response = new BinaryFileResponse($tempFileName);
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                'downloaded_files.zip'
            );
            //dd($response);
            return $response;
        } catch (\Exception $e) {
            // Vous pouvez enregistrer l'erreur dans un journal, l'afficher, etc.
            error_log($e->getMessage());
            return $this->json('Une erreur est survenue lors de la création du fichier zip', 500);
        }
    }
}
