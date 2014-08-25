<?php

namespace Btn\MediaBundle\Form;

use Btn\AdminBundle\Form\AbstractForm as BaseAbstractForm;

abstract class AbstractForm extends BaseAbstractForm
{
    /** @var string $actionRouteName */
    protected $actionRouteName = '';

    /** @var string $actionRouteParams */
    protected $actionRouteParams = array();

    /**
     * set form action route name
     * @param string $routeName
     */
    public function setActionRouteName($routeName)
    {
        $this->actionRouteName = $routeName;
    }

    /**
     * get form action route name
     * @param
     * @return string
     */
    public function getActionRouteName()
    {
        return $this->actionRouteName;
    }

    /**
     * Set form action route params
     * @param array $routeParams
     */
    public function setActionRouteParams($routeParams)
    {
        $this->actionRouteParams = $routeParams;
    }

    /**
     * Get form action route params
     * @return array $routeParams
     */
    public function getActionRouteParams()
    {
        return $this->actionRouteParams;
    }
}
