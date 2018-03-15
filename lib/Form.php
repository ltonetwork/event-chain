<?php

use Jasny\FormBuilder;
use Jasny\FormGenerator;

/**
 * Base class for forms
 */
abstract class Form extends FormBuilder\Form
{
    protected $customTypes = [
        'file' => ['bootstrap/fileinput'],
        'image' => ['bootstrap/imageinput'],
        'container' => ['bootstrap/container']
    ];
    
    /**
     * Class constructor
     * 
     * @param array $options  Form options
     * @param array $attr     HTML attributes
     */
    public function __construct(array $options = [], array $attr = [])
    {
        self::registerBootstrap();
        
        parent::__construct($options, $attr);
        
        $this
            ->addDecorator('bootstrap', ['version'=>3])
            ->addClass('form-horizontal')
            ->setOption('grid', ['col-sm-2', 'col-sm-10'])
        ;
    }

    /**
     * Build submit/cancel buttons
     * 
     * @return Form $this
     */
    protected function buildFormActions()
    {
        $container = $this->begin('div', ['name'=>'form-actions', 'container'=>false, 'grid'=>false],
            ['class'=>'form-actions form-group']);
        $grid = $container->begin('div', [], ['class'=>'col-sm-10 col-sm-offset-2']);
        
        $cancel = $this->getOption('cancel');
        if ($cancel) {
            $grid->add('link', [
                'description' => 'Cancel',
                'url' => $cancel,
                'btn' => 'default lg'
            ]);
        }
        
        $grid->add('button', [
            'name' => 'submit',
            'description' => 'Save',
            'btn' => 'primary lg labeled',
            'prepend' => FormBuilder\Bootstrap::icon('check', 'fa')
        ]);
        
        return $container;
    }
    
    /**
     * Get the errors of all the controls.
     * Errors are set by validation.
     * 
     * @return array
     * 
     * @todo Should be in Jasny\FormBuilder\Group
     */
    public function getErrors()
    {
        $errors = [];
        
        foreach ($this->getControls() as $control) {
            $error = $control->getError();
            if ($error) $errors[$control->getName()] = $error;
        }
        
        return $errors;
    }
    
    /**
     * Enable to form generator
     */
    public static function enableGenerator()
    {
        // Enable generator
        if (App::config()->cache) set_include_path(get_include_path() . PATH_SEPARATOR . BASE_PATH . '/cache/model');
        
        FormGenerator::$parentClass = __CLASS__;
        FormGenerator::enable(DB::conn(), BASE_PATH . '/cache/forms');
    }

    /**
     * Register bootstrap
     */
    protected static function registerBootstrap()
    {
        if (isset(FormBuilder::$decorators['bootstrap'])) return;
        
        FormBuilder\Bootstrap::register();
        
        FormBuilder::$elements['container'][2]['class'] = 'form-group';
        
        FormBuilder::$elements['choicelist'] = FormBuilder::$elements['choice'];
        FormBuilder::$elements['choice'] = FormBuilder::$elements['select'];
    }
}
