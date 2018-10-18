<?php
/**
 * Created by PhpStorm.
 * User: stoyan.kalinov
 * Date: 11.10.2018 г.
 * Time: 9:17
 */

namespace App\Controller;


use App\Entity\Category;
use App\Entity\QuestionReaction;
use App\Entity\ReactionReason;
use App\Form\CategoryFormType;
use App\Repository\CategoryRepository;
use App\Repository\QuestionAnswerRepository;
use App\Repository\QuestionReactionRepository;
use App\Repository\ReactionReasonRepository;
use Doctrine\ORM\EntityManagerInterface;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;


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
        $dislikeReactions = $reactionReason->findBy(['reaction_category' => 'dislike']);

        if(!$entities)
        {
            return $this->render('404_not_found.html.twig', [
                'message' => 'Category not found'
            ]);
        }

        return $this->render('faqMainCategories.html.twig', [
            'main' => $main,
            'entities' => $entities,
            'dislikeReactions' => $dislikeReactions
        ]);
    }

    /**
     * @Route("/faq/reaction/like", defaults={"_category": "like"}, name="question_reaction_like", methods={"POST"})
     */
    public function like($_category, Request $request, QuestionAnswerRepository $qa, ReactionReasonRepository $reactionReason)
    {
        $data = json_decode($request->getContent(), true);

        if($data === null || empty($data))
        {
            throw new BadRequestHttpException('Invalid JSON');
        }

        $question = $qa->findOneBy(['id' => $data['id']]);
        $reason = $reactionReason->findOneBy(['reason' => 'Like', 'reaction_category' => $_category]);

        $qaReaction = new QuestionReaction();
        $qaReaction->setQuestion($question)->setReaction($reason);

        $this->getDoctrine()->getManager()->persist($qaReaction);
        $this->getDoctrine()->getManager()->flush();

        return $this->json([
            'question' => $question->getId(),
            'reason' => $reason->getId()
        ]);
    }


    /**
     * @Route("/faq/reaction/dislike", name="question_reaction_dislike", methods={"POST"})
     */
    public function dislike(Request $request, QuestionAnswerRepository $qa, ReactionReasonRepository $reactionReason)
    {
        $data = json_decode($request->getContent(), true);

        if($data === null || empty($data))
        {
            throw new BadRequestHttpException('Invalid JSON');
        }

        $question = $qa->findOneBy(['id' => $data['id']]);
        $reason = $reactionReason->findOneBy(['reason' => 'Like', 'reaction_category' => $_category]);

        $qaReaction = new QuestionReaction();
        $qaReaction->setQuestion($question)->setReaction($reason);

        $this->getDoctrine()->getManager()->persist($qaReaction);
        $this->getDoctrine()->getManager()->flush();

        return $this->json([
            'question' => $question->getId(),
            'reason' => $reason->getId()
        ]);
    }
    /**
     * @Route("/faq/new", name="client_newsub_category")
     * @Route("/faq/new", name="notclient_newsub_category")
     */
    public function neww()
    {
        $form = $this->createForm(CategoryFormType::class);

        return $this->render('formtest.html.twig', [
           'formtest' => $form->createView()
        ]);
    }


    /**
     * @Route("/faq/statistic/questions/{question_id}", name="statistic")
     */
    public function statisticForQuestions($question_id, QuestionAnswerRepository $qaRepo, ReactionReasonRepository $reactionReason)
    {
        $questionObj = $qaRepo->findOneBy(['id' => $question_id]);
        if ($questionObj === null)
        {
            return $this->render('404_not_found.html.twig', ['message' => 'No question with such ID']);
        }

        $questionReactions = $questionObj->getQuestionReactions();

        $reasons = $reactionReason->getReasonsAsCategories();

        foreach ($questionReactions as $reactionObj)
        {
            $qaReactionMainCategory = $reactionObj->getReaction()->getReactionCategory();
            $qaReactionReason = $reactionObj->getReaction()->getReason();

            $reasons[$qaReactionMainCategory][$qaReactionReason]++;
        }
        dd($reasons);
    }

    /**
     * @Route("/faq/statistic/question-statistic", name="question_statistic", methods={"POST"})
     */
    public function statisticForQuestion(Request $request, QuestionAnswerRepository $qaRepo, ReactionReasonRepository $reactionReason)
    {
        $data = json_decode($request->getContent(), true);

        if($data === null || empty($data))
        {
            throw new BadRequestHttpException('Invalid JSON');
        }
        $questionObj = $qaRepo->findOneBy(['id' => $data['id']]);
        if ($questionObj === null)
        {
            $errors[] = 'No question with such ID';
        }

        $questionReactions = $questionObj->getQuestionReactions();

        $reasons = $reactionReason->getReasonsAsCategories();

        foreach ($questionReactions as $reactionObj)
        {
            $qaReactionMainCategory = $reactionObj->getReaction()->getReactionCategory();
            $qaReactionReason = $reactionObj->getReaction()->getReason();

            $reasons[$qaReactionMainCategory][$qaReactionReason]++;
        }

        return $this->json([
            'statistic' => $reasons
        ]);
    }

}