<?php

namespace Jupitern\Ctt;

use Exception;
use Goutte\Client;

class CttTracking
{
	public $refsPerRequest = 25; // maximum number of references per request. max allowed by ctt is 25
	public $waitAfterRequest = 0; // seconds

	const STATUS_PENDING = 1;
	const STATUS_IN_TRANSIT = 2;
	const STATUS_DELIVERY_PENDING = 3;
	const STATUS_DELIVERED = 4;
	const STATUS_RETURNED = 5;
	const STATUS_UNKNOWN_OBJ = 6;

	private $baseUrl = 'http://www.ctt.pt/feapl_2/app/open/objectSearch/objectSearch.jspx';
	private $possibleStatus = [
		'Objeto aceite' => self::STATUS_PENDING,
		'Objeto expedido' => self::STATUS_IN_TRANSIT,
		'Objeto em distribuição' => self::STATUS_IN_TRANSIT,
		'Disponível para Levantamento' => self::STATUS_DELIVERY_PENDING,
		'Objeto com tentativa de entrega' => self::STATUS_DELIVERY_PENDING,
		'Objeto entregue' => self::STATUS_DELIVERED,
		'Objeto devolvido' => self::STATUS_RETURNED,
		'Objeto não encontrado' => self::STATUS_UNKNOWN_OBJ,
	];

	/**
	 * @param array $refs
	 * @return array
	 */
	public function trackObjects(array $refs = [])
	{
		$returnArr = [];
		$requestRefs = '';

		$j = 1;
		foreach ($refs as $idx => $ref) {
			$requestRefs .= ($j++ > 1 ? ',' : '') . $ref;

			if ($j == $this->refsPerRequest) {
				$returnArr += $this->makeRequest($requestRefs);
				if ((int)$this->waitAfterRequest > 0) {
					sleep($this->waitAfterRequest);
				}
				$requestRefs = '';
				$j = 1;
			}
		}

		if ($requestRefs != "") {
			$returnArr += $this->makeRequest($requestRefs);
		}

		return $returnArr;
	}


	/**
	 * @param $statusCode
	 * @return string
	 */
	public static function getStatusString($statusCode)
	{
		$class = new \ReflectionClass(__CLASS__);
		$constants = array_flip($class->getConstants());

		return $constants[$statusCode];
	}


	/**
	 * @param $requestRefs
	 * @return array
	 */
	private function makeRequest($requestRefs)
	{
		$returnArr = [];
		$client = new Client();
		$res = $client->request('POST', $this->baseUrl, [
			'showResults' => true,
			'objects' => $requestRefs
		]);

		$res->filterXPath('//*[@id="objectSearchResult"]/table/tr')->each(function($node, $i) use(&$returnArr) {
			$resultText = trim($node->text());
			if (strpos($resultText, '[+]Info') !== false) {

				foreach ($this->possibleStatus as $statusText => $status) {
					if (strpos($resultText, $statusText) !== false) {
						$objRef = trim(substr($resultText, 0, strpos($resultText, ' ')-1));
						$returnArr[trim($objRef)] = ['status' => $status, 'statusText' => $statusText];
					}
				}
			}
		});

		return $returnArr;
	}

}
