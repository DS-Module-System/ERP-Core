<?php

namespace App\Controller\User;

use App\Entity\User\BaseUser;
use App\Form\User\MeForm;
use App\Form\User\UserForm;
use App\Form\User\UserSearchForm;
use App\Repository\User\BaseUserRepository;
use App\Service\Core\CoreUtils;
use App\Service\Core\RoleManager;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\User\UserChangePasswordForm;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/users')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly RoleManager $roleManager
    )
    {
    }
    #[Route(path: '', name: 'user_list')]
    #[IsGranted('ROLE_USER_VIEW')] 
    public function list(Request $request, EntityManagerInterface $em, PaginatorInterface $paginator): Response
    {
        $page = $request->get('page', 1);
        $cmsRedirect = CoreUtils::setDefaultOrderInList($request, ['email'=>'ASC']);
        if($cmsRedirect !== null) {
            return $cmsRedirect;
        }

        $searchFormData = [];
        $form = $this->createForm(UserSearchForm::class, null, ['csrf_protection' => false]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $searchFormData = $form->getData();
        }
        if ($request->isMethod('DELETE')) {
            $listPostData = $request->request->all();
            if (isset($listPostData['do']['listDelete'])) {
                $ids = explode(',', (string) $listPostData['ids']);
                foreach ($ids as $id) {
                    $row = $em->getRepository(BaseUser::class)->find($id);
                    $em->remove($row);
                }
                $em->flush();
                return $this->redirectToRoute('user_list');
            }

        }

        $searchFormData[CoreUtils::$SORT_LIST_QUERY_NAME] = $request->query->all(CoreUtils::$SORT_LIST_QUERY_NAME);
        $searchFormData['baseUserType'] = 'admin';

        /** @var BaseUserRepository $userRepository */
        $userRepository = $em->getRepository(BaseUser::class);
        $users = $paginator->paginate(
            $userRepository->getPaginatedUserQuery($searchFormData),
            $page,
            $request->query->get('ipp') ?: $this->getParameter('datatable_ipp_default')
        );
        return $this->render('user/index.html.twig', [
            'rows' => $users,
            'form' => $form->createView(),
        ]);
    }
    #[Route(path: '/create', name: 'user_create')]
    #[IsGranted('ROLE_USER_CREATE')] 
    public function create(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $encoder): Response
    {
        $user = new BaseUser();
        $form = $this->createForm(UserForm::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var SubmitButton $saveBtn
             */
            $saveBtn = $form->get('save');
            if ($saveBtn->isClicked()) {
                $user->setPassword($encoder->hashPassword($user, $user->getPassword()));
                $user->setTrustedTokenVersion(0);
                $em->persist($user);
                try {
                    $em->flush();
                    $this->addFlash('success', $this->translator->trans('core.layout.successCreateFlashMsg'));
                    return CoreUtils::redirect($request, $this->redirectToRoute('user_list'));
                } catch (UniqueConstraintViolationException) {
                    $this->addFlash('error',
                        $this->translator->trans('user.errorEmailFlashMsg') . ' ' . $user->getEmail() . '!');
                }
            }
        }
        return $this->render('user/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route(path: '/{id}/edit', name: 'user_edit')]
    #[IsGranted('ROLE_USER_EDIT')] 
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $row = $em->getRepository(BaseUser::class)->find($id);
        $form = $this->createForm(UserForm::class, $row);
        $form->remove('password');
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var SubmitButton $deleteBtn
             */
            $deleteBtn = $form->get('delete');
            if ($deleteBtn->isClicked()) {
                /**
                 * @var BaseUser $row
                 */
//                $row->removeGroups($row->getGroups());
                $em->remove($row);
                try {
                    $em->flush();
                } catch (ForeignKeyConstraintViolationException) {
                    $this->addFlash('warning', $this->translator->trans('layout.errorDeleteFlashMsg'));
                    return CoreUtils::redirect($request, $this->redirectToRoute('user_list'));
                }
                $this->addFlash('warning', $this->translator->trans('layout.deleteFlashMsg'));
                return $this->redirectToRoute('user_list');
            } else {
                /**
                 * @var SubmitButton $saveBtn
                 */
                $saveBtn = $form->get('save');
                if ($saveBtn->isClicked()) {
                    try {
                        $em->flush();
                        $this->addFlash('success', $this->translator->trans('layout.successEditFlashMsg'));
                        return CoreUtils::redirect($request, $this->redirectToRoute('user_list'));
                    } catch (UniqueConstraintViolationException) {
                        $this->addFlash('error',
                            $this->translator->trans('user.errorEmailFlashMsg') . ' ' . $row->getEmail() . '!');
                    }
                }
            }
        }
        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route(path: '/{id}/change-password', name: 'user_change_password')]
        #[IsGranted('ROLE_USER_EDIT')] 
    public function changePassword(int $id, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $encoder): Response
    {
        $row = $em->getRepository(BaseUser::class)->find($id);
        $form = $this->createForm(UserChangePasswordForm::class, $row);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var SubmitButton $saveBtn
             */
            $saveBtn = $form->get('save');
            if ($saveBtn->isClicked()) {
                $row->setPassword($encoder->hashPassword($row, $row->getPassword()));
                $em->persist($row);
                $em->flush();
                $this->addFlash('success', $this->translator->trans('user.successChangePasswordFLashMsg'));
                return $this->redirectToRoute('user_list');
            }
        }
        return $this->render('user/change_password.html.twig', [
            'form' => $form->createView(),
            'user' => $row,
        ]);
    }

    #[Route(path: '/roles', name: 'user_roles')]
    #[IsGranted('ROLE_USER_VIEW')] 
    public function roles(): Response
    {
        $allRoles = $this->roleManager->getAllRoles();
        $rolesByModule = $this->roleManager->getRolesByModule();
        $roleHierarchy = $this->roleManager->getRoleHierarchy();

        return $this->render('user/roles.html.twig', [
            'allRoles' => $allRoles,
            'rolesByModule' => $rolesByModule,
            'roleHierarchy' => $roleHierarchy,
        ]);
    }

    #[Route(path: '/deletes', name: 'user_deletes', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER_DELETE')] 
    public function deletes(Request $request, EntityManagerInterface $em): RedirectResponse
    {
        if ($request->isMethod('DELETE')) {
            $ids = $request->request->all()['ids'];
            if (!empty($ids)) {
                $ids = explode(',', (string) $ids);
                foreach ($ids as $id) {
                    /**
                     * @var BaseUser $row
                     */
                    $row = $em->getRepository(BaseUser::class)->find($id);
//                    $row->removeUserGroup($row->getUserGroups());
                    $em->remove($row);
                }
                $em->flush();
                $this->addFlash('warning', $this->translator->trans('layout.deleteFlashMsg'));
            }
        }
        return $this->redirectToRoute('user_list');
    }
    #[Route(path: '/me', name: 'user_me')]
    #[IsGranted('ROLE_USER_VIEW')] 
    public function me(Request $request, EntityManagerInterface $em, Security $security, UserPasswordHasherInterface $passwordHasher): Response
    {
        $row = $em->getRepository(BaseUser::class)->find($security->getUser());
        $form = $this->createForm(MeForm::class, $row);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var SubmitButton $saveBtn
             */
            $saveBtn = $form->get('save');
            if ($saveBtn->isClicked()) {
                $em->flush();
                $this->addFlash('success', $this->translator->trans('layout.successEditFlashMsg'));
                return $this->redirectToRoute('user_me');
            }
        }
        $changePasswordForm = $this->createForm(UserChangePasswordForm::class);
        $changePasswordForm->handleRequest($request);
        if ($changePasswordForm->isSubmitted() && $changePasswordForm->isValid()) {
            /**
             * @var SubmitButton $saveBtn
            */
            $saveBtn = $changePasswordForm->get('save');
            if ($saveBtn->isClicked()) {
                $data = $changePasswordForm->getData();
                $row->setPassword($passwordHasher->hashPassword($row, $data['password']));
                $em->persist($row);
                $em->flush();
                $this->addFlash('success', $this->translator->trans('user.successChangePasswordFLashMsg'));
                return $this->redirectToRoute('user_me');
            }
        }
        return $this->render('user/me.html.twig', [
            'form' => $form->createView(),
            'user' => $row,
            'changePasswordForm' => $changePasswordForm->createView(),
        ]);
    }

    #[Route(path: '/change-user-theme-mode', name: 'user_change_user_theme_mode')]
    #[IsGranted('ROLE_USER_EDIT')] 
    public function changeUserThemeMode(Request $request, EntityManagerInterface $em): RedirectResponse
    {

        $user = $em->getRepository(BaseUser::class)->find($this->getUser());
        if($user) {
            $post = $request->request->all();

            if (isset($post['submit1']) && $post['submit1'] == 'submit1') {
                if (!isset($post['isLightMode'])) {
                    $user->setIsDarkTheme(true);
                    setcookie("devscale_panel_dark_mode", '1', ['expires' => time() + (2 * 365 * 24 * 60 * 60), 'path' => '/']);
                } else {
                    $user->setIsDarkTheme(false);
                    setcookie('devscale_panel_dark_mode', '', ['expires' => -1, 'path' => '/']);
                }
                $em->persist($user);
                $em->flush();
            }
        }

        $backUrl = $request->get('backUrl', null);
        if($backUrl) {
            return $this->redirect($backUrl);
        }
        return $this->redirectToRoute('dashboard');

    }
} 