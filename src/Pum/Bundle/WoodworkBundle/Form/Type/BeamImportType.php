<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Guzzle\Http\Client;
use Guzzle\Http\Exception\ClientException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\File;
use Pum\Bundle\WoodworkBundle\Validation\Constraints\BeamArchiveStructure;

class BeamImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text')
            ->add('file', 'file', array(
                'constraints' => array(
                    new NotBlank(array(
                        'message' => 'Please select a file',
                        'groups' => array('Import')
                    )),
                    new BeamArchiveStructure(
                        array(
                            'groups' => array('Import')
                        )
                    )
                ),
                'required' => false,
                'mapped' => false
            ))
            ->add('import', 'submit')
            ->add('url', 'hidden');

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            if ($data['url'] != null) {
                $client = new Client();
                $path = sys_get_temp_dir() .'/'. md5(mt_rand());
                try {
                    $client
                        ->get($data['url'], null, ['save_to' => $path])
                        ->send()
                    ;
                    $data['file'] = new UploadedFile($path, $data['url'], 'application/zip');
                    $event->setData($data);
                } catch (ClientException $exception) {
                    $event->getForm()->get('url')->addError(new FormError('ZipNotFound'));
                }
            }
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => array('Import'),
            'translation_domain' => 'pum_form'
        ));
    }

    public function getName()
    {
        return 'ww_beam_import';
    }
}
