<?php

use PHPUnit\Framework\TestCase;
use App\Entity\User;
use App\Security\GithubUserProvider;
use GuzzleHttp\Client;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class GithubUserProviderTest extends TestCase {

    private MockObject | Client | null $client;
    private MockObject | Serializer | null $serializer;
    private MockObject | StreamInterface | null $streamedResponse;
    private MockObject | ResponseInterface | null $response;

    public function setUp(): void {
        $this->client = $this->getMockBuilder('GuzzleHttp\Client')->disableOriginalConstructor()->getMock();
        $this->serializer = $this->getMockBuilder('JMS\Serializer\SerializerInterface')->disableOriginalConstructor()->getMock();
        $this->response = $this->getMockBuilder('Psr\Http\Message\ResponseInterface')->getMock();
        $this->streamedResponse = $this->getMockBuilder('Psr\Http\Message\StreamInterface')->getMock();
    }

    function testRenvoitLoadUserByUsername(){
        
      

        $this->response->expects($this->once())->method('getBody')->willReturn($this->streamedResponse);
        $this->streamedResponse->expects($this->once())->method('getContents')->willReturn('foo');
        
        $this->client->expects($this->once())->method('get')->willReturn($this->response);

        $userData = ['login' => 'a login', 'name' => 'user name', 'email' => 'adress@mail.com', 'avatar_url' => 'url to the avatar', 'html_url' => 'url to profile'];
        $this->serializer->expects($this->once())->method('deserialize')->willReturn($userData);

        $githubUserProvider = new GithubUserProvider($this->client, $this->serializer);
        $user = $githubUserProvider->loadUserByUsername('xxxxx');
        $expectedUser = new User($userData['login'], $userData['name'], $userData['email'], $userData['avatar_url'], $userData['html_url']);

        $this->assertEquals($expectedUser, $user);
        $this->assertEquals('App\Entity\User', get_class($user));
    }

    function testRenvoitExceptionLoadUserByUsername(){

        $this->response->expects($this->once())->method('getBody')->willReturn($this->streamedResponse);
        $this->streamedResponse->expects($this->once())->method('getContents')->willReturn('foo');
        
        $this->client->expects($this->once())->method('get')->willReturn($this->response);

        //$userData = ['login' => 'a login', 'name' => 'user name', 'email' => 'adress@mail.com', 'avatar_url' => 'url to the avatar', 'html_url' => 'url to profile'];
        $this->serializer->expects($this->once())->method('deserialize')->willReturn([]);

        
        $this->expectException(JMS\Serializer\Exception\LogicException::class);

        $githubUserProvider = new GithubUserProvider($this->client, $this->serializer);
        $user = $githubUserProvider->loadUserByUsername('xxxxx');
        //$expectedUser = new User($userData['login'], $userData['name'], $userData['email'], $userData['avatar_url'], $userData['html_url']);

    }

    public function tearDown() : void

    {
        
        $this->client = null;
        $this->serializer = null;
        $this->streamedResponse = null;
        $this->response = null;

    }
}