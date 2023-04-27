<?php

namespace App\Controller;

use App\Entity\Message;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\Array_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Routing\Annotation\Route;

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/../../.env');

class ContactController extends AbstractController
{
    /**
     * Constructor by default
     */
    public function __construct()
    {
    }

    // /**
    //  * @Route("/", name="app_contact")
    //  */
    // public function index(): Response
    // {
    //     return $this->render('contact/index.html.twig', [
    //         'controller_name' => 'ContactController',
    //     ]);
    // }

    /**
     * @Route("/contact", methods={"POST"})
     */
    function contactAction(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        // Retrieve form data from the request
        $expediteur = $request->request->get('expediteur');
        $messageBody = $request->request->get('message');
        $email = $request->request->get('email');

        if ($expediteur == null || $messageBody == null || $email == null) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Please fill in all the fields.'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Check the reCAPTCHA response
        $recaptchaResponse = $request->request->get('g-recaptcha-response');
        if (!empty($recaptchaResponse)) {
            $secretAPIkey = getenv('RECAPTCHA_KEY');
            $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secretAPIkey . '&response=' . $recaptchaResponse);
            $response = json_decode($verifyResponse);

            if ($response->success) {
                // Insert the data into the database
                $em = $doctrine->getManager();
                $asMessage = new Message();
                $asMessage->setMessage($messageBody);
                $asMessage->setEmail($email);
                $asMessage->setExpediteur($expediteur);
                $em->persist($asMessage);
                $em->flush();

                // Get the timestamp of the message
                $createdAt = $asMessage->getCreatedAt();

                // From sender email address
                $headers = "From: contact@loic-leprieur.fr\r\n";

                // Body of the email
                $body = "<table>
                            <thead>
                                <tr>
                                <th>Expéditeur</th>
                                <th>Email</th>
                                <th>Date</th>
                                <th>Message</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                <td>{{ $expediteur }}</td>
                                <td>{{ $email }}</td>
                                <td>{{ $createdAt }}</td>
                                <td>{{ $messageBody }}</td>
                                </tr>
                            </tbody>
                            </table>";

                // Send the email
                mail('contact@loic-leprieur.fr', 'Message de loic-leprieur.fr', $body, $headers);

                // Return a success response
                return new JsonResponse([
                    'status' => 'success',
                    'message' => 'Your message was sent successfully!'
                ], Response::HTTP_OK);
            } else {
                // Return an error response if the reCAPTCHA check fails
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Robot verification failed, please try again.'
                ], Response::HTTP_BAD_REQUEST);
            }
        } else {
            // Return an error response if the reCAPTCHA field is not present in the request
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Missing reCAPTCHA field.'
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
