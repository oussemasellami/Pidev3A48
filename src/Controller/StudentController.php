<?php

namespace App\Controller;

use App\Entity\Student;
use App\Form\StudentType;
use App\Repository\StudentRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/student')]
class StudentController extends AbstractController
{
    #[Route('/', name: 'app_student_index', methods: ['GET'])]
    public function index(StudentRepository $studentRepository): Response
    {
        return $this->render('student/index.html.twig', [
            'students' => $studentRepository->findAll(),
        ]);
    }

    #[Route('/json', name: 'app')]
    public function index2(StudentRepository $studentRepository, NormalizerInterface $normalizer)
    {
        $students = $studentRepository->findAll();
        $studentNormalize = $normalizer->normalize($students, 'json', ['groups' => 'students']);
        $json = json_encode($studentNormalize);
        return new Response($json);
    }
    #[Route('/newJson', name: 'app_student_newJson', methods: ['GET', 'POST'])]
    public function newJson(Request $request, NormalizerInterface $normalizer, StudentRepository $studentRepository): Response
    {
        $student = new Student();
        $student->setNsc($request->get('nsc'));
        $student->setEmail($request->get('email'));
        $studentRepository->save($student, true);
        $json_content = $normalizer->normalize($student, 'json', ['groups' => 'students']);
        return new Response(json_encode($json_content));
    }

    #[Route('/new', name: 'app_student_new', methods: ['GET', 'POST'])]
    public function new(Request $request, StudentRepository $studentRepository): Response
    {
        $student = new Student();
        $form = $this->createForm(StudentType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $studentRepository->save($student, true);

            return $this->redirectToRoute('app_student_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('student/new.html.twig', [
            'student' => $student,
            'form' => $form,
        ]);
    }

    //json with id
    #[Route('/json/{id}', name: 'app_student_showJson', methods: ['GET'])]
    public function showJson(Student $student, NormalizerInterface $normalizer, StudentRepository $studentRepository): Response
    {
        $student = $studentRepository->find($student);
        $studentNormalize = $normalizer->normalize($student, 'json', ['groups' => 'students']);
        $json = json_encode($studentNormalize);
        return new Response($json);
    }

    #[Route('/{id}', name: 'app_student_show', methods: ['GET'])]
    public function show(Student $student): Response
    {
        return $this->render('student/show.html.twig', [
            'student' => $student,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_student_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Student $student, StudentRepository $studentRepository): Response
    {
        $form = $this->createForm(StudentType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $studentRepository->save($student, true);

            return $this->redirectToRoute('app_student_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('student/edit.html.twig', [
            'student' => $student,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_student_delete', methods: ['POST'])]
    public function delete(Request $request, Student $student, StudentRepository $studentRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $student->getId(), $request->request->get('_token'))) {
            $studentRepository->remove($student, true);
        }

        return $this->redirectToRoute('app_student_index', [], Response::HTTP_SEE_OTHER);
    }
}
