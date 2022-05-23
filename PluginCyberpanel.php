<?php

require_once 'plugins/server/cyberpanel/lib/api.php';

class PluginCyberpanel extends ServerPlugin
{

    public $features = [
        'packageName' => true,
        'testConnection' => true,
        'showNameservers' => false,
        'directlink' => false,
        'upgrades' => true
    ];

    private $api;
    private function setup($args)
    {
        $this->api = new CyberAPI(
            $args['server']['variables']['plugin_cyberpanel_Admin_Username'],
            $args['server']['variables']['plugin_cyberpanel_Admin_Password'],
            $args['server']['variables']['ServerHostName'],
            $args['server']['variables']['plugin_cyberpanel_Port']
        );
    }

    public function getVariables()
    {
        $variables = [
            'Name' => [
                'type' => 'hidden',
                'description' => 'Used by CE to show plugin',
                'value' => 'CyberPanel'
            ],
            'Description' => [
                'type' => 'hidden',
                'description' => 'Description viewable by admin in server settings',
                'value' => 'CyberPanel Server Integration'
            ],
            'Admin Username' => [
                'type' => 'text',
                'description' => 'CyberPanel Admin Username',
                'value' => '',
            ],
            'Admin Password' => [
                'type' => 'password',
                'description' => 'CyberPanel Admin Password',
                'value' => '',
                'encryptable' => true
            ],
            'Port' => [
                'type' => 'text',
                'description' => 'CyberPanel Port',
                'value' => '8090',
            ],
            'Actions' => [
                'type' => 'hidden',
                'description' => 'Current actions that are active for this plugin per server',
                'value'=>'Create,Delete,Suspend,UnSuspend'
            ],
            'Registered Actions For Customer' => [
                'type' => 'hidden',
                'description' => 'Current actions that are active for this plugin per server for customers',
                'value' => ''
            ],
            'package_addons' => [
                'type' => 'hidden',
                'description' => 'Supported signup addons variables',
                'value' => []
            ],
            'package_vars' => [
                'type' => 'hidden',
                'description' => 'Whether package settings are set',
                'value' => '1',
            ],
            'package_vars_values' => [
                'type'  => 'hidden',
                'description' => lang('Package Settings'),
                'value' => [
                    'ACL' => [
                        'type' => 'text',
                        'label' => 'ACL',
                        'description' => 'ACL for user',
                        'value' => 'user',
                    ]
                ]
            ]
        ];

        return $variables;
    }

    public function validateCredentials($args)
    {
    }

    public function doDelete($args)
    {
        $userPackage = new UserPackage($args['userPackageId']);
        $args = $this->buildParams($userPackage);
        $this->delete($args);
        return 'Package has been deleted.';
    }

    public function doCreate($args)
    {
        $userPackage = new UserPackage($args['userPackageId']);
        $args = $this->buildParams($userPackage);
        $this->create($args);
        return 'Package has been created.';
    }

    public function doUpdate($args)
    {
        $userPackage = new UserPackage($args['userPackageId']);
        $args = $this->buildParams($userPackage);
        $this->update($args);
        return 'Package has been updated.';
    }

    public function doSuspend($args)
    {
        $userPackage = new UserPackage($args['userPackageId']);
        $args = $this->buildParams($userPackage);
        $this->suspend($args);
        return 'Package has been suspended.';
    }

    public function doUnSuspend($args)
    {
        $userPackage = new UserPackage($args['userPackageId']);
        $args = $this->buildParams($userPackage);
        $this->unsuspend($args);
        return 'Package has been unsuspended.';
    }

    public function unsuspend($args)
    {
        $this->setup($args);
        $this->api->changeAccountStatus(
            $args['package']['domain_name'],
            'Unsuspend'
        );
    }

    public function suspend($args)
    {
        $this->setup($args);
        $this->api->changeAccountStatus(
            $args['package']['domain_name'],
            'Suspend'
        );
    }

    public function delete($args)
    {
        $this->setup($args);
        $this->api->terminateAccount(
            $args['package']['domain_name']
        );
    }

    public function update($args)
    {
        $this->setup($args);
        foreach ($args['changes'] as $key => $value) {
            switch ($key) {
                case 'password':
                    $this->api->changeAccountPassword(
                        $args['package']['username'],
                        $value
                    );
                    break;
                case 'package':
                    $this->api->changeAccountPackage(
                        $args['package']['domain_name'],
                        $values
                    );
                    break;
            }
        }
    }

    public function create($args)
    {
        $this->setup($args);
        $userPackage = new UserPackage($args['package']['id']);

        $this->api->createAccount(
            $args['package']['domain_name'],
            $args['customer']['email'],
            $args['package']['name_on_server'],
            $args['package']['username'],
            $args['package']['password'],
            $args['package']['variables']['ACL']
        );
    }

    public function testConnection($args)
    {
        $this->setup($args);
        CE_Lib::log(4, 'Testing connection to CyberPanel');
        $this->api->verifyConnection();
    }
}
