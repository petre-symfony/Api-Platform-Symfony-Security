<?php


namespace App\Tests\Functional;


use App\Entity\User;
use App\Test\CustomApiTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class UserResourceTest extends CustomApiTestCase {
	use ReloadDatabaseTrait;

	public function testCreateUser(){
		$client = self::createClient();

		$client->request('POST', '/api/users', [
			'json' => [
				'email' => 'cheeselover@examole.com',
				'username' => 'cheeselover',
				'password' => 'foo'
			]
		]);

		$this->assertResponseStatusCodeSame(201);

		$this->logIn($client, 'cheeselover@examole.com', 'foo');

	}

	public function testUpdateUser(){
		$client = self::createClient();
		$user = $this->createUserAndLogIn($client, 'user@example.com', 'foo');
		$client->request('PUT', '/api/users/'.$user->getId(), [
			'json' => [
				'username' => 'cheeselover',
				'roles' => ['ROLE_ADMIN'] //will be ignored
			]
		]);
		$this->assertResponseIsSuccessful();
		$this->assertJsonContains([
			'username' => 'cheeselover'
		]);

		$em = $this->getEntityManager();
		/** @var User $user */
		$user = $em->getRepository(User::class)->find($user->getId());
		$this->assertEquals(['ROLE_USER'], $user->getRoles());
	}

	public function testGetUser(){
		$client = self::createClient();
		$user = $this->createUser('user@example.com', 'foo');
		$this->createUserAndLogIn($client, 'authenticated@example.com', 'foo');

		$user->setPhoneNumber('555.123.4567');
		$em = $this->getEntityManager();
		$em->flush();
		$client->request("GET", "/api/users/".$user->getId());
		$this->assertJsonContains([
			'username' => 'user'
		]);
		$data = $client->getResponse()->toArray();
		$this->assertArrayNotHasKey('phoneNumber', $data);

		// refresh the user & elevate
		$user = $em->getRepository(User::class)->find($user->getId());
		$user->setRoles(['ROLE_ADMIN']);
		$em->flush();
		$this->logIn($client, 'user@example.com', 'foo');

		$client->request("GET", "/api/users/".$user->getId());
		$this->assertJsonContains([
			'phoneNumber' => '555.123.4567'
		]);
	}
}