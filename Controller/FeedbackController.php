<?php
/**
 * @package Newscoop\EditorBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\EditorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Newscoop\EditorBundle\Form\Type\FeedbackType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class FeedbackController extends Controller
{
    /**
     * @Route("/editor_plugin/feedback/", options={"expose"=true}, name="aes_send_feedback")
     * @Method("POST")
     */
    public function submitAction(Request $request)
    {
        $form = $this->get('form.factory')->create(new FeedbackType(), array(), array());
        $translator = $this->get('translator');
        $form->handleRequest($request);
        $jsonResponse = new JsonResponse();
        $jsonResponse->headers->set('Access-Control-Allow-Origin', '*');
        $jsonResponse->setData(array(
            'status' => false,
            'message' => $translator->trans('aes.msgs.feedback.error'),
        ));

        $jsonResponse->setStatusCode(422);

        if ($form->isValid()) {
            $data = $form->getData();
            $this->get('email')->send(
                'Feedback from: '.$data['name'],
                $data['message'],
                'aes@sourcefabric.org',
                $data['email']
            );

            $jsonResponse->setData(array(
                'status' => true,
                'message' => $translator->trans('aes.msgs.feedback.success'),
            ));

            $jsonResponse->setStatusCode(200);
        }

        return $jsonResponse;
    }
}
