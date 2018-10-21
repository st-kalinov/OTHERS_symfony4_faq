<?php
/**
 * Created by PhpStorm.
 * User: stoyan.kalinov
 * Date: 11.10.2018 г.
 * Time: 9:17
 */

namespace App\Controller;


use App\Entity\Category;
use App\Entity\QuestionAnswer;
use App\Entity\QuestionReaction;
use App\Entity\ReactionReason;
use App\Form\CategoryFormType;
use App\Repository\CategoryRepository;
use App\Repository\QuestionAnswerRepository;
use App\Repository\QuestionReactionRepository;
use App\Repository\ReactionReasonRepository;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class FaqController extends AbstractController
{
    /**
     * @Route("/faq", name="faq")
     */
    public function index()
    {
        $entity = $this->getDoctrine()->getManager()->getRepository(Category::class);
        $entities = $entity->findBy(['parent_id' => null]);


        if(!$entities)
        {
            return $this->render('404_not_found.html.twig', [
                'message' => 'No MAIN Categories'
            ]);
        }

        return $this->render('faq.html.twig', [
            'entities' => $entities
        ]);
    }

    /**
     * @Route("/faq/{main}/{c}", name="subcategories1", methods={"GET"})
     * @return Response
     */
    public function subcategories1($main, CategoryRepository $categoryRepository, Request $request, ReactionReasonRepository $reactionReason)
    {

        $c = $request->get('c');

        //get the subcategories objects if there are any
        $entities = $categoryRepository->findBy(['parent_id' => $c]);

        $reactionsAsCategories = $reactionReason->getReactionsObjAsCategories();

        if(!$entities)
        {
            return $this->render('404_not_found.html.twig', [
                'message' => 'Category not found'
            ]);
        }

        return $this->render('faqMainCategories.html.twig', [
            'main' => $main,
            'entities' => $entities,
            'reactions' => $reactionsAsCategories
        ]);
    }

    /**
     * @Route("/faq/reaction", name="question_reaction", methods={"POST"})
     */
    public function questionReaction(Request $request, QuestionAnswerRepository $qa, ReactionReasonRepository $reactionReason)
    {
        $data = json_decode($request->getContent(), true);

        if($data === null || empty($data) || count($data) < 2)
        {
            throw new BadRequestHttpException('Invalid JSON');
        }

        $question = $qa->find(['id' => $data['questionId']]);
        if(!$question)
        {
            throw $this->createNotFoundException('Invalid question');
        }
        unset($data['questionId']);
        $reasons = [];
        foreach ($data as $key => $id) {
            $reasons[] = $reactionReason->find(['id' => $data[$key]]);
        }

        if(empty($reasons))
        {
            throw $this->createNotFoundException('Invalid data');
        }

        foreach ($reasons as $reason) {
            $qaReaction = new QuestionReaction();
            $qaReaction->setQuestion($question)->setReaction($reason);
            $this->getDoctrine()->getManager()->persist($qaReaction);
        }

        $this->getDoctrine()->getManager()->flush();
        return $this->json([
            'message' => 'Thanks'
        ]);

    }

    /**
     * @Route("/faq/statistic/question-statistic", name="question_statistic", methods={"POST"})
     */
    public function statisticForQuestion(Request $request, QuestionAnswerRepository $qaRepo, ReactionReasonRepository $reactionReason, QuestionReactionRepository $questionReactionRepository)
    {
        $data = json_decode($request->getContent(), true);

        if($data === null || empty($data))
        {
            throw new BadRequestHttpException('Invalid JSON');
        }
        $questionObj = $qaRepo->findOneBy(['id' => $data['id']]);
        if ($questionObj === null)
        {
            throw $this->createNotFoundException('Invalid question');
        }

        $questionReactions = $questionObj->getQuestionReactions();

        $reasons = $reactionReason->getReasonsNamesAsCategories();

        foreach ($questionReactions as $questionReactionObj)
        {
            $qaReactionMainCategory = $questionReactionObj->getReaction()->getReactionCategory();
            $qaReactionReason = $questionReactionObj->getReaction()->getReason();

            $reasons[$qaReactionMainCategory][$qaReactionReason]++;
        }

        return $this->json([
            'statistic' => $reasons
        ]);
    }

}