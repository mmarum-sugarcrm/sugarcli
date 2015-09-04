<?php

namespace SugarCli\Tests\Inventory;

use Guzzle\Tests\GuzzleTestCase;
use Guzzle\Service\Client as GClient;
use Guzzle\Service\Description\ServiceDescription;

class MockClientTest extends ClientTestCase
{
    public $fqdn = 'testserver.inetprocess.fr';

    public $name = "FooBar";

    public function getClientType()
    {
        return 'mock';
    }

    public function getClient($mocks = array())
    {
        $client = parent::getClient($mocks);
        $client->setDescription(
            ServiceDescription::factory(__DIR__ . '/../../src/Inventory/InventoryService.json')
        );
        return $client;
    }

    public function testPostServer()
    {
        $client = $this->getClient(array('server/post.http'));
        $cmd = $client->getCommand('postServer', array(
            'fqdn' => $this->fqdn,
            'facts' => array('test' => 'test', 'foo' => 'bar')
        ));
        $resp = $cmd->execute();
        $this->assertEquals(201, $cmd->getResponse()->getStatusCode());
        $this->assertStringEndsWith('/servers/' . $this->fqdn, $resp->get('location'));
    }

    public function testPutServer()
    {
        $client = $this->getClient(array('server/put.http'));
        $cmd = $client->getCommand('putServer', array(
            'fqdn_uri' => $this->fqdn,
            'fqdn' => $this->fqdn,
            'facts' => array('test' => 'test', 'bar' => 'foo')
        ));
        $cmd->execute();
        $this->assertEquals(204, $cmd->getResponse()->getStatusCode());
    }

    public function testGetOneServer()
    {
        $client = $this->getClient(array('server/get_one.http'));
        $cmd = $client->getCommand('getServer', (array('fqdn' => $this->fqdn)));
        $resp = $cmd->execute();
        $this->assertEquals($this->fqdn, $resp['fqdn']);
    }

    public function testGetServers()
    {
        $client = $this->getClient(array('server/get.http'));
        $resp = $client->getServers();
        foreach ($resp as $server) {
            $this->assertArrayHasKey('id', $server);
            $this->assertArrayHasKey('fqdn', $server);
            $this->assertArrayHasKey('facts', $server);
            $this->assertArrayHasKey('sugar_instances', $server);
        }
    }

    public function testDeleteServer()
    {
        $client = $this->getClient(array('server/delete.http'));
        $cmd = $client->getCommand('deleteServer', array('fqdn' => $this->fqdn));
        $cmd->execute();
        $this->assertEquals(204, $cmd->getResponse()->getStatusCode());
    }

    /*******************************
     ******* Account ***************
     *******************************/


    public function testPostAccount()
    {
        $client = $this->getClient(array('account/post.http'));
        $cmd = $client->getCommand('postAccount', array(
            'name' => $this->name,
        ));
        $resp = $cmd->execute();
        $this->assertEquals(201, $cmd->getResponse()->getStatusCode());
        $this->assertStringEndsWith('/accounts/' . $this->name, $resp->get('location'));
    }

    public function testPutAccount()
    {
        $client = $this->getClient(array('account/put.http'));
        $cmd = $client->getCommand('putAccount', array(
            'name_uri' => $this->name,
            'name' => $this->name,
        ));
        $cmd->execute();
        $this->assertEquals(204, $cmd->getResponse()->getStatusCode());
    }

    public function testGetOneAccount()
    {
        $client = $this->getClient(array('account/get_one.http'));
        $cmd = $client->getCommand('getAccount', (array('name' => $this->name)));
        $resp = $cmd->execute();
        $this->assertEquals($this->name, $resp['name']);
    }

    public function testGetAccounts()
    {
        $client = $this->getClient(array('account/get.http'));
        $resp = $client->getAccounts();
        foreach ($resp as $account) {
            $this->assertArrayHasKey('id', $account);
            $this->assertArrayHasKey('name', $account);
            $this->assertArrayHasKey('sugar_instances', $account);
        }
    }

    public function testDeleteAccount()
    {
        $client = $this->getClient(array('account/delete.http'));
        $cmd = $client->getCommand('deleteAccount', array('name' => $this->name));
        $cmd->execute();
        $this->assertEquals(204, $cmd->getResponse()->getStatusCode());
    }
}
