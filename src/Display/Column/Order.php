<?php

namespace SleepingOwl\Admin\Display\Column;

use Illuminate\Routing\Router;
use Illuminate\Database\Eloquent\Model;
use SleepingOwl\Admin\Display\TableColumn;
use SleepingOwl\Admin\Traits\OrderableModel;
use SleepingOwl\Admin\Contracts\WithRoutesInterface;

class Order extends TableColumn implements WithRoutesInterface
{
    /**
     * @var bool
     */
    protected $orderable = false;

    /**
     * @var string
     */
    protected $view = 'column.order';

    /**
     * Register routes.
     *
     * @param Router $router
     */
    public static function registerRoutes(Router $router)
    {
        $routeName = 'admin.display.column.move-up';
        if (! $router->has($routeName)) {
            $router->group(['namespace' => 'SleepingOwl\Admin\Http\Controllers\DisplayColumnController'],
                function ($router) use ($routeName) {
                    $router->post('{adminModel}/{adminModelId}/up', [
                        'as'   => $routeName,
                        'uses' => 'DisplayColumnController@orderUp',
                    ]);
                });
        }

        $routeName = 'admin.display.column.move-down';
        if (! $router->has($routeName)) {
            $router->group(['namespace' => 'SleepingOwl\Admin\Http\Controllers\DisplayColumnController'],
                function ($router) use ($routeName) {
                    $router->post('{adminModel}/{adminModelId}/down', [
                        'as'   => $routeName,
                        'uses' => 'DisplayColumnController@orderDown',
                    ]);
                });
        }
    }

    /**
     * Order constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setHtmlAttribute('class', 'row-order');
    }

    /**
     * @return Model $model
     * @throws \Exception
     */
    public function getModel()
    {
        if (! in_array(OrderableModel::class, trait_uses_recursive($class = get_class($this->model)))) {
            throw new \Exception("Model [$class] should uses trait [SleepingOwl\\Admin\\Traits\\OrderableModel]");
        }

        return $this->model;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    protected function getOrderValue()
    {
        return $this->getModel()->getOrderValue();
    }

    /**
     * @return mixed
     */
    protected function totalCount()
    {
        return $this->getModelConfiguration()->getRepository()->getQuery()->count();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    protected function movableUp()
    {
        return $this->getOrderValue() > 0;
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function moveUpUrl()
    {
        return route('admin.display.column.move-up', [
            $this->getModelConfiguration()->getAlias(),
            $this->getModel()->getKey(),
        ]);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    protected function movableDown()
    {
        return $this->getOrderValue() < $this->totalCount() - 1;
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function moveDownUrl()
    {
        return route('admin.display.column.move-down', [
            $this->getModelConfiguration()->getAlias(),
            $this->getModel()->getKey(),
        ]);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function toArray()
    {
        return parent::toArray() + [
                'movableUp'   => $this->movableUp(),
                'moveUpUrl'   => $this->moveUpUrl(),
                'movableDown' => $this->movableDown(),
                'moveDownUrl' => $this->moveDownUrl(),
            ];
    }
}
