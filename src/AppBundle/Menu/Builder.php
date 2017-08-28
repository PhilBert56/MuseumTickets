<?php

namespace AppBundle\Menu;

use Knp\Menu\MenuFactory;

class Builder
{
    public function mainMenu(MenuFactory $factory, array $options){

        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav navbar-nav');
        $menu->addChild('Accueil', ['route' => 'accueil']);
        $menu->addChild('Nouvelle commande', ['route' => 'ordertickets']);
        $menu->addChild('Tests', ['route' => 'test']);
        return $menu;

    }
}
