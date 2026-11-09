<?php

namespace App\Controller\User;

use App\Entity\User\UserGroup;
use App\Form\User\UserGroupForm;
use App\Form\User\UserGroupSearchForm;
use App\Repository\User\UserGroupRepository;
use App\Service\Core\RoleManager;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\User\RoleLoader;

#[Route(path: '/user-groups')]
class UserGroupController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly RoleManager $roleManager
    )
    {
    }
    #[Route(path: '', name: 'user_group_list')]
    #[IsGranted('ROLE_USER_GROUP_VIEW')] 
    public function list(Request $request, EntityManagerInterface $em, PaginatorInterface $paginator): Response
    {
        $page = $request->get('page', 1);
        $searchFormData = [];
        $form = $this->createForm(UserGroupSearchForm::class, null, ['csrf_protection' => false]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $searchFormData = $form->getData();
        }
        /** @var UserGroupRepository $userGroupRepository */
        $userGroupRepository = $em->getRepository(UserGroup::class);
        $userGroups = $paginator->paginate(
            $userGroupRepository->getPaginatedUserGroupQuery($searchFormData),
            $page,
            $request->query->get('ipp') ?: $this->getParameter('datatable_ipp_default')
        );
        return $this->render('user/user_group/index.html.twig', [
            'rows' => $userGroups,
            'form' => $form->createView(),
            'roles' => $this->roleManager->getAllRoles(),
        ]);
    }
    
    #[Route(path: '/create', name: 'user_group_create')]
    #[IsGranted('ROLE_USER_GROUP_CREATE')] 
    public function create(Request $request, EntityManagerInterface $em, RoleLoader $roleLoader): Response
    {
        $row = new UserGroup();

        // Grouped (original) roles
        $roleGroups = $roleLoader->loadRoles();

        // Flatten + invert for Symfony ChoiceType
        $flat = [];
        foreach ($roleGroups as $group => $roles) {
            foreach ($roles as $key => $label) {
                $flat[$label] = $key; // IMPORTANT: Symfony wants "Label" => "value"
            }
        }

        $form = $this->createForm(UserGroupForm::class, $row, [
            'role_groups' => $roleGroups,
            'roles' => $flat     // <-- FIXED
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $saveBtn = $form->get('save');
            if ($saveBtn->isClicked()) {
                $em->persist($row);
                try {
                    $em->flush();
                    $this->addFlash('success', $this->translator->trans('core.layout.successCreateFlashMsg'));
                    return $this->redirectToRoute('user_group_list');
                } catch (UniqueConstraintViolationException) {
                    $this->addFlash('error', $this->translator->trans('userGroup.errorSameNameFlashMsg').' '.$row->getName().'!');
                }
            }
        }

        return $this->render('user/user_group/create.html.twig', [
            'form' => $form->createView(),
            'role_groups' => $roleGroups
        ]);
    }

    #[Route(path: '/{id<\d+>?-1}/edit', name: 'user_group_edit')]
    #[IsGranted('ROLE_USER_GROUP_EDIT')] 
    public function edit(Request $request, EntityManagerInterface $em, RoleLoader $roleLoader, int $id): Response
    {
        $row = $em->getRepository(UserGroup::class)->find($id);
        $preparedRoles = [];
        foreach ($this->roleManager->getAllRoles() as $key => $role) {
            $preparedRoles[$key] = $this->translator->trans($role);
        }
        $roleGroups = $roleLoader->loadRoles();

        // Flatten + invert for Symfony ChoiceType
        $flat = [];
        foreach ($roleGroups as $group => $roles) {
            foreach ($roles as $key => $label) {
                $flat[$label] = $key; // IMPORTANT: Symfony wants "Label" => "value"
            }
        }

        $form = $this->createForm(UserGroupForm::class, $row, [
            'role_groups' => $roleGroups,
            'roles' => $flat     // <-- FIXED
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var SubmitButton $deleteBtn
            */
            $deleteBtn = $form->get('delete');
            if ($deleteBtn->isClicked()) {
                $em->remove($row);
                $em->flush();
                $this->addFlash('warning', $this->translator->trans('layout.deleteFlashMsg'));
                return $this->redirectToRoute('user_group_list');
            }

            /**
             * @var SubmitButton $saveBtn
             */
            $saveBtn = $form->get('save');
            if ($saveBtn->isClicked()) {
                try {
                    $em->flush();
                    $this->addFlash('success', $this->translator->trans('layout.successEditFlashMsg'));
                    return $this->redirectToRoute('user_group_list');
                } catch (UniqueConstraintViolationException) {
                    $this->addFlash('error', $this->translator->trans('userGroup.errorSameNameFlashMsg').' '.$row->getName().'!');
                }
            }
        }
        return $this->render('user/user_group/edit.html.twig', [
            'form' => $form->createView(),
            'role_groups' => $roleGroups
        ]);
    }

    #[Route(path: '/deletes', name: 'user_group_deletes')]
    #[IsGranted('ROLE_USER_GROUP_DELETE')] 
    public function deletes(Request $request, EntityManagerInterface $em): RedirectResponse
    {
        if ($request->isMethod('DELETE')) {
            $ids = $request->request->all()['ids'];
            if (!empty($ids)) {
                $ids = explode(',', (string) $ids);
                foreach ($ids as $id) {
                    $product = $em->getRepository(UserGroup::class)->find($id);
                    $em->remove($product);
                }
                $em->flush();
                $this->addFlash('warning', $this->translator->trans('layout.deleteFlashMsg'));
            }
        }
        return $this->redirectToRoute('user_group_list');
    }
} 