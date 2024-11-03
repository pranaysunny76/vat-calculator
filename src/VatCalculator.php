<?php 

namespace Mpociot\VatCalculator;

use Illuminate\Contracts\Config\Repository;
use Mpociot\VatCalculator\Exceptions\VATCheckUnavailableException;
use SoapClient;
use SoapFault;

class VatCalculator
{
    const VAT_SERVICE_URL = 'https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';
    protected $soapClient;
    protected $taxRules = [
        'RO' => [ 
            'rate' => 0.19, 
        ],
       
    ];
    public function __construct($config = null)
    {
        $this->config = $config;

        $businessCountryKey = 'vat_calculator.business_country_code';

        if (isset($this->config) && $this->config->has($businessCountryKey)) {
            $this->setBusinessCountryCode($this->config->get($businessCountryKey, ''));
        }
    }

    public function calculate($netPrice, $countryCode = null, $postalCode = null, $company = null)
    {
        if ($countryCode) {
            $this->setCountryCode($countryCode);
        }

        if ($postalCode) {
            $this->setPostalCode($postalCode);
        }

        if ($company !== null && $company !== $this->isCompany()) {
            $this->setCompany($company);
        }

        $this->netPrice = floatval($netPrice);
        $this->taxRate = $this->getTaxRateForLocation($this->getCountryCode(), $this->getPostalCode(), $this->isCompany());
        $this->taxValue = round($this->taxRate * $this->netPrice, 2);
        $this->value = round($this->netPrice + $this->taxValue, 2);

        return $this->value;
    }

    public function calculateNet($gross, $countryCode = null, $postalCode = null, $company = null)
    {
        if ($countryCode) {
            $this->setCountryCode($countryCode);
        }

        if ($postalCode) {
            $this->setPostalCode($postalCode);
        }

        if ($company !== null && $company !== $this->isCompany()) {
            $this->setCompany($company);
        }

        $this->value = floatval($gross);
        $this->taxRate = $this->getTaxRateForLocation($this->getCountryCode(), $this->getPostalCode(), $this->isCompany());
        $this->taxValue = round($this->taxRate > 0 ? $this->value / (1 + $this->taxRate) * $this->taxRate : 0, 2);
        $this->netPrice = round($this->value - $this->taxValue, 2);

        return $this->netPrice;
    }
    public function initSoapClient()
    {
        if (is_object($this->soapClient) || $this->soapClient === false) {
            return;
        }
        $timeout = 30;

        if (isset($this->config) && $this->config->has('vat_calculator.soap_timeout')) {
            $timeout = $this->config->get('vat_calculator.soap_timeout');
        }

        $context = stream_context_create(['http' => ['timeout' => $timeout]]);

        try {
            $this->soapClient = new SoapClient(self::VAT_SERVICE_URL, ['stream_context' => $context]);
        } catch (SoapFault $e) {
            if (isset($this->config) && $this->config->get('vat_calculator.forward_soap_faults')) {
                throw new VATCheckUnavailableException($e->getMessage(), $e->getCode(), $e->getPrevious());
            }

            $this->soapClient = false;
        }
    }
    public function testing()
    {
        $this->ukValidationEndpoint = 'https://test-api.service.hmrc.gov.uk';

        return $this;
    }
}
