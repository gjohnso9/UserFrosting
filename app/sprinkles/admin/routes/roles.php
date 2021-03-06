<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */

/**
 * Routes for administrative role management.
 */
$app->group('/admin/roles', function () {
    $this->get('', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:pageList')
        ->setName('uri_roles');

    $this->get('/r/{slug}', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:pageInfo');
})->add('authGuard');

$app->group('/api/roles', function () {
    $this->delete('/r/{slug}', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:delete');

    $this->get('', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:getList');

    $this->get('/r/{slug}', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:getInfo');

    $this->get('/r/{slug}/permissions', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:getPermissions');

    $this->post('', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:create');

    $this->put('/r/{slug}', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:updateInfo');

    $this->put('/r/{slug}/{field}', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:updateField');
})->add('authGuard');

$app->group('/modals/roles', function () {
    $this->get('/confirm-delete', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:getModalConfirmDelete');

    $this->get('/create', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:getModalCreate');

    $this->get('/edit', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:getModalEdit');

    $this->get('/permissions', 'UserFrosting\Sprinkle\Admin\Controller\RoleController:getModalEditPermissions');
})->add('authGuard');
