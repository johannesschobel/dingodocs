<?php

namespace JohannesSchobel\DingoDocs\Generators;

use JohannesSchobel\DingoDocs\Models\Route;

class DingoGenerator extends AbstractGenerator
{
    /**
     * @param \Illuminate\Routing\Route $route
     *
     * @return \JohannesSchobel\DingoDocs\Models\Route
     */
    public function processRoute($route)
    {
        $action = $route->getAction();
        $description = $this->getRouteDescriptionAnnotation($action['uses']);

        $title = $route->uri();
        if(!empty($description['title'])) {
            $title = $description['title'];
        }

        $model                  = new Route();
        $model->id              = md5($route->uri() . ':' . implode($route->getMethods()));
        $model->uri             = $route->uri();
        $model->methods         = $route->getMethods();
        $model->group           = $this->getRouteGroupAnnotation($action['uses']);
        $model->title           = $title;
        $model->description     = $description['description'];
        $model->authenticated   = $this->getRouteAuthenticatedAnnotation($action['uses']);
        $model->status          = $this->getRouteStatusCodeAnnotation($action['uses']);;
        $model->transformer     = $this->getRouteTransformerAnnotation($action['uses']);
        $model->validator       = $this->getRouteValidator($action['uses']);
        $model->parameters      = $this->getRouteValidationParameters($model, $model->validator);
        $model->request         = $this->getRouteRequestAnnotation($action['uses']);

        return $model;

    }
}
