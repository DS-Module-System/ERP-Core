<?php

namespace App\Controller\Core;

use App\Entity\Core\CoreEntity;
use App\Repository\Core\CoreRepository;
use App\Service\Core\CoreUtils;
use App\Service\Core\ContextService;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Query;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class CoreBaseController extends AbstractController
{
    protected string $searchFormClass;
    protected string $entityClass;
    protected string $moduleTemplateName;
    protected string $formClass;

    /**
     * @var array<mixed> $defaultOrderList
     */
    protected array $defaultOrderList = [];

    /**
     * @var array<mixed> $additionalData
     */
    protected array $additionalData = [];

    /**
     * @var array<mixed> $appendSearchFormData
     */
    protected array $appendSearchFormData = [];
    protected ?RedirectResponse $redirectAfterCreate = null;
    protected ?RedirectResponse $redirectAfterEdit = null;
    protected ?RedirectResponse $redirectAfterDelete = null;

    /**
     * @var array<mixed> $callbacks
     */
    protected array $callbacks = [];

    protected bool $isModalRequest = false;

    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly TranslatorInterface    $translator,
        protected readonly PaginatorInterface     $paginator,
        protected readonly ContextService         $contextService,
    ) {}


    /**
     * baseList
     *
     * @param  mixed $request
     * @param  mixed $page
     * @return render("{$this->moduleTemplateName}/index.html.twig
     */
    public function baseList(Request $request, int $page): Response
    {

        if (!empty($this->defaultOrderList)) {
            $coreRedirect = CoreUtils::setDefaultOrderInList($request, $this->defaultOrderList);
            if ($coreRedirect !== null) {
                return $coreRedirect;
            }
        }

        $searchFormData = [];

        $form = null;
        if ($this->searchFormClass) {
            $form = $this->createForm($this->searchFormClass, null, ['csrf_protection' => false]);

            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                $searchFormData = $form->getData();
            }
        }

        $searchFormData[CoreUtils::$SORT_LIST_QUERY_NAME] = $request->query->all(CoreUtils::$SORT_LIST_QUERY_NAME);

        if (!empty($this->appendSearchFormData)) {
            $searchFormData = array_merge($searchFormData, $this->appendSearchFormData);
        }

        if (isset($this->callbacks['changeSearchFormData']) && is_callable($this->callbacks['changeSearchFormData'])) {
            $searchFormData = $this->callbacks['changeSearchFormData']($searchFormData);
        } else {
            if (method_exists($this, 'changeSearchFormData')) {
                $searchFormData = call_user_func([$this, 'changeSearchFormData'], $searchFormData);
            }
        }

        $query = null;
        if (isset($this->callbacks['setListQuery']) && $this->callbacks['setListQuery']) {
            $query = $this->callbacks['setListQuery']($searchFormData);
        } else {
            /**
             * @var CoreRepository $coreRep
             */
            $coreRep = $this->em->getRepository($this->entityClass);
            $query = $coreRep->getPaginatedQuery($searchFormData);
        }

        $rows = $this->paginator->paginate(
            $query,
            $page,
            $request->query->get('ipp') ?: $this->getParameter('datatable_ipp_default')
        );

        $additionalData = $this->additionalData;
        if (empty($additionalData)) {
            if (method_exists($this, 'setAdditionalListTemplateData')) {
                $additionalData = call_user_func([$this, 'setAdditionalListTemplateData'], $rows);
            }
        }

        return $this->render("{$this->moduleTemplateName}/index.html.twig", [
            'rows' => $rows,
            'form' => $form ? $form->createView() : null,
            'additionalData' => $additionalData,
        ]);
    }


    /**
     * baseCreate
     *
     * Creates forms with its handlers
     * 
     * return $this->render("{$this->moduleTemplateName}/form.html.twig",
     * 
     */
    public function baseCreate(Request $request): Response
    {
        $row = new $this->entityClass();

        if (isset($this->callbacks['setDefaultEntityData']) && is_callable($this->callbacks['setDefaultEntityData'])) {
            $row = $this->callbacks['setDefaultEntityData']($row, $this->additionalData, $request);
            if ($row instanceof RedirectResponse) {
                return $row;
            }
        } else {
            if (method_exists($this, 'setDefaultEntityData')) {
                $row = call_user_func([$this, 'setDefaultEntityData'], $row, $this->additionalData, $request);
                if ($row instanceof RedirectResponse) {
                    return $row;
                }
            }
        }

        $formOptions = [];
        if (isset($this->callbacks['setFormOptions']) && is_callable($this->callbacks['setFormOptions'])) {
            $formOptions = $this->callbacks['setFormOptions']($row);
        } else {
            if (method_exists($this, 'setFormOptions')) {
                $formOptions = call_user_func([$this, 'setFormOptions'], $row);
            }
        }

        $form = $this->createForm($this->formClass, $row, $formOptions);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            /**
             * @var SubmitButton $saveBtn
             */
            $saveBtn = $form->get('save');
            if ($saveBtn->isClicked() && $form->isValid()) {

                if (isset($this->callbacks['preCreatePersist']) && is_callable($this->callbacks['preCreatePersist'])) {
                    $row = $this->callbacks['preCreatePersist']($row);
                } else {
                    if (method_exists($this, 'preCreatePersist')) {
                        $row = call_user_func([$this, 'preCreatePersist'], $row);
                    }
                }

                $this->em->persist($row);
                try {
                    $this->em->flush();

                    if (isset($this->callbacks['postCreateFlush']) && is_callable($this->callbacks['postCreateFlush'])) {
                        $row = $this->callbacks['postCreateFlush']($row);
                    } else {
                        if (method_exists($this, 'postCreateFlush')) {
                            call_user_func([$this, 'postCreateFlush'], $row);
                        }
                    }

                    if ($this->contextService->isModalRequest() || $this->isModalRequest) {
                        return $this->json(['success' => true]);
                    } else {
                        $this->addFlash('success', $this->translator->trans('core.layout.successCreateFlashMsg'));
                        $redirectAfterCreate = '';
                        if ($this->redirectAfterCreate) {
                            $redirectAfterCreate = $this->redirectAfterCreate;
                        } elseif (isset($this->callbacks['redirectAfterCreate']) && is_callable($this->callbacks['redirectAfterCreate'])) {
                            $redirectAfterCreate = $this->callbacks['redirectAfterCreate']($row);
                        } else {
                            $redirectAfterCreate = $this->redirectToRoute("{$this->moduleTemplateName}_list");
                        }
                        return CoreUtils::redirect($request, $redirectAfterCreate);
                    }
                } catch (\Exception $e) {
                    throw $e;
                }
            }
        }

        $additionalData = $this->additionalData;
        if (empty($additionalData)) {
            if (method_exists($this, 'setAdditionalFormTemplateData')) {
                $additionalData = call_user_func([$this, 'setAdditionalFormTemplateData'], $row, $additionalData);
            }
        }

        return $this->render("{$this->moduleTemplateName}/form.html.twig", [
            'form' => $form->createView(),
            'row' => $row,
            'additionalData' => $additionalData,
        ]);
    }

    /**
     * baseEdit
     *
     * Creates edit forms with its handlers
     * 
     */
    public function baseEdit(Request $request, int $id): Response
    {
        $row = null;
        if (isset($this->callbacks['getRow']) && is_callable($this->callbacks['getRow'])) {
            $row = $this->callbacks['getRow']($id);
        } else {
            $row = $this->em->getRepository($this->entityClass)->find($id);
        }

        if (isset($this->callbacks['preEditCreateForm']) && is_callable($this->callbacks['preEditCreateForm'])) {
            $this->callbacks['preEditCreateForm']($row);
        } else {
            if (method_exists($this, 'preEditCreateForm')) {
                call_user_func([$this, 'preEditCreateForm'], $row);
            }
        }

        $formOptions = [];
        if (isset($this->callbacks['setFormOptions']) && is_callable($this->callbacks['setFormOptions'])) {
            $formOptions = $this->callbacks['setFormOptions']($row);
        } else {
            if (method_exists($this, 'setFormOptions')) {
                $formOptions = call_user_func([$this, 'setFormOptions'], $row);
            }
        }

        $form = $this->createForm($this->formClass, $row, $formOptions);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            /**
             * @var SubmitButton $deleteBtn
             */
            $deleteBtn = $form->get('delete');
            if ($deleteBtn->isClicked()) {

                if (isset($this->callbacks['preRemove']) && is_callable($this->callbacks['preRemove'])) {
                    $row = $this->callbacks['preRemove']($row);
                } else {
                    if (method_exists($this, 'preRemove')) {
                        $row = call_user_func([$this, 'preRemove'], $row);
                    }
                }

                $this->em->remove($row);
                try {
                    $this->em->flush();

                    if (isset($this->callbacks['postRemoveFlush']) && is_callable($this->callbacks['postRemoveFlush'])) {
                        $this->callbacks['postRemoveFlush']($row);
                    } else {
                        if (method_exists($this, 'postRemoveFlush')) {
                            call_user_func([$this, 'postRemoveFlush'], $row);
                        }
                    }
                } catch (ForeignKeyConstraintViolationException) {
                    if ($this->contextService->isModalRequest() || $this->isModalRequest) {
                        return $this->json(['success' => false, 'message' => $this->translator->trans('layout.errorDeleteFlashMsg')]);
                    } else {
                        $this->addFlash('warning', $this->translator->trans('layout.errorDeleteFlashMsg'));
                        $redirectAfterDelete = $this->redirectAfterDelete;
                        if (!$redirectAfterDelete) {
                            $redirectAfterDelete = $this->redirectToRoute("{$this->moduleTemplateName}_list");
                        }
                        return CoreUtils::redirect($request, $redirectAfterDelete);
                    }
                }

                if ($this->contextService->isModalRequest() || $this->isModalRequest) {
                    return $this->json(['success' => true]);
                } else {
                    $this->addFlash('warning', $this->translator->trans('layout.deleteFlashMsg'));
                    $redirectAfterDelete = $this->redirectAfterDelete;
                    if (!$redirectAfterDelete) {
                        $redirectAfterDelete = $this->redirectToRoute("{$this->moduleTemplateName}_list");
                    }
                    return CoreUtils::redirect($request, $redirectAfterDelete);
                }
            } else {
                /**
                 * @var SubmitButton $saveBtn
                 */
                $saveBtn = $form->get('save');
                if ($saveBtn->isClicked() && $form->isValid()) {

                    if (isset($this->callbacks['preEditPersist']) && is_callable($this->callbacks['preEditPersist'])) {
                        $row = $this->callbacks['preEditPersist']($row);
                    } else {
                        if (method_exists($this, 'preEditPersist')) {
                            $row = call_user_func([$this, 'preEditPersist'], $row);
                        }
                    }

                    try {
                        $this->em->flush();

                        if (isset($this->callbacks['postEditFlush']) && is_callable($this->callbacks['postEditFlush'])) {
                            $row = $this->callbacks['postEditFlush']($row);
                        } else {
                            if (method_exists($this, 'postEditFlush')) {
                                call_user_func([$this, 'postEditFlush'], $row);
                            }
                        }

                        if ($this->contextService->isModalRequest() || $this->isModalRequest) {
                            return $this->json(['success' => true]);
                        } else {
                            $this->addFlash('success', $this->translator->trans('layout.successEditFlashMsg'));
                            $redirectAfterEdit = '';
                            if ($this->redirectAfterEdit) {
                                $redirectAfterEdit = $this->redirectAfterEdit;
                            } elseif (isset($this->callbacks['redirectAfterEdit']) && is_callable($this->callbacks['redirectAfterEdit'])) {
                                $redirectAfterEdit = $this->callbacks['redirectAfterEdit']($row);
                            } else {
                                $redirectAfterEdit = $this->redirectToRoute("{$this->moduleTemplateName}_list");
                            }
                            return CoreUtils::redirect($request, $redirectAfterEdit);
                        }
                    } catch (\Exception $e) {
                        throw $e;
                    }
                }
            }
        }

        $additionalData = $this->additionalData;
        if (empty($additionalData)) {
            if (method_exists($this, 'setAdditionalFormTemplateData')) {
                $additionalData = call_user_func([$this, 'setAdditionalFormTemplateData'], $row, $additionalData);
            }
        }

        return $this->render("{$this->moduleTemplateName}/form.html.twig", [
            'form' => $form->createView(),
            'row' => $row,
            'additionalData' => $additionalData,
        ]);
    }

    /**
     * baseDeletes
     *
     * Handles deletion of entity items
     * Redirects to list page of the same modul
     *  
     */
    public function baseDeletes(Request $request): Response
    {
        if ($request->isMethod('DELETE')) {
            $ids = $request->request->all()['ids'];
            if (!empty($ids)) {
                $ids = explode(',', (string) $ids);
                foreach ($ids as $id) {
                    $row = $this->em->getRepository($this->entityClass)->find($id);
                    if (isset($this->callbacks['preRemove']) && is_callable($this->callbacks['preRemove'])) {
                        $row = $this->callbacks['preRemove']($row);
                    } else {
                        if (method_exists($this, 'preRemove')) {
                            $row = call_user_func([$this, 'preRemove'], $row);
                        }
                    }
                    $this->em->remove($row);
                }
                $this->em->flush();
                $this->addFlash('warning', $this->translator->trans('layout.deleteFlashMsg'));
            }
        }
        $redirectAfterDelete = $this->redirectAfterDelete;
        if (!$redirectAfterDelete) {
            $redirectAfterDelete = $this->redirectToRoute("{$this->moduleTemplateName}_list");
        }
        return $redirectAfterDelete;
    }
}
