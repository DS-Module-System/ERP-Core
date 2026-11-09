<?php
/**
 * Created by PhpStorm.
 * User: Ervin
 * Date: 2019-07-22
 * Time: 17:36
 */

namespace App\Form\Core\DefaultForm;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class EditForm extends AbstractType
{

//    private ?array $files = null;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod(\Symfony\Component\HttpFoundation\Request::METHOD_POST)
            ->add('save', SubmitType::class, [
                'label' => 'core.layout.saveBtnTitle',
                'attr' => [
                    'class' => 'btn btn-success',
                ],
                'icon_before' => 'mdi mdi-content-save me-1',
            ])
            ->add('delete', SubmitType::class, [
                'label' => 'core.layout.deleteBtnTitle',
                'attr' => [
                    'class' => 'btn btn-danger',
                ],
                'icon_before' => 'mdi mdi-delete me-1',
            ])
        ;

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            $this->onPreSetData(...)
        );
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            $this->onPreSubmit(...)
        );
    }

    public function onPreSetData(FormEvent $event): void
    {
        $formData = $event->getData();
        $form = $event->getForm();

        if (!$formData) {
            return;
        }

        foreach ($form->all() as $fieldName => $fieldType) {
            if($fieldType->getConfig()->getType()->getInnerType() instanceof FileAssociationCollectionType) {
                $t = [];
                foreach (call_user_func([$formData, 'get'.ucfirst($fieldName)]) as $v) {
                    if($v->getFile()) {
                        $t[] = $v->getFile()->getId();
                    }
                }
//                $this->files[$fieldName] = $t;

                $fieldNameTextConfig = [
                    'mapped' => false,
                    'data' => empty($t) ? '' : implode(',', $t),
                ];

                if($fieldType->getConfig()->getOptions()) {
                    $attributes = $fieldType->getConfig()->getOptions();
                    if(isset($attributes['attr'])) {
                        $fieldNameTextConfig['attr'] = $attributes['attr'];
                        unset($fieldNameTextConfig['attr']['data-original-files-hidden']);
                    }
                    if(isset($attributes['label'])) {
                        $fieldNameTextConfig['label'] = $attributes['label'];
                    }
                    if(isset($attributes['required'])) {
                        $fieldNameTextConfig['required'] = $attributes['required'];
                    }
                }

                $form->add("{$fieldName}Text", TextType::class, $fieldNameTextConfig);
            }
        }
    }

    public function onPreSubmit(FormEvent $event): void
    {
        $formData = $event->getData();
        $form = $event->getForm();

        foreach ($form->all() as $fieldName => $fieldType) {
            if ($fieldType->getConfig()->getType()->getInnerType() instanceof FileAssociationCollectionType) {
                $tmpImages = [];

                $arr = [];
                if (!empty($formData["{$fieldName}Text"])) {
                    $arr = explode(',', (string) $formData["{$fieldName}Text"]);
                    foreach ($arr as $k => $v) {
                        $tmpImages[] = [
                            'file' => $v,
                            'position' => $k + 1,
                        ];
                    }
                }

//                $toDel = array_diff($this->files[$fieldName], $arr);
//                if($toDel) {
//                    foreach ($toDel as $k => $v) {
//                        array_splice( $tmpImages, $k, 0, [null] );
//                    }
//                }

//dd($tmpImages);
                $formData[$fieldName] = $tmpImages;
            }
        }

        $event->setData($formData);

    }
}
