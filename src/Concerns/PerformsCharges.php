<?php

namespace Frkcn\Kasiyer\Concerns;

use Frkcn\Kasiyer\Kasiyer;
use Iyzipay\Model\Address;
use Iyzipay\Model\BasketItem;
use Iyzipay\Model\BasketItemType;
use Iyzipay\Model\Buyer;
use Iyzipay\Model\CheckoutFormInitialize;
use Iyzipay\Model\PaymentGroup;
use Iyzipay\Request\CreateCheckoutFormInitializeRequest;

trait PerformsCharges
{
    /**
     * The buyer info for the single charge.
     *
     * @var \Iyzipay\Model\Buyer
     */
    protected $buyer;

    /**
     * The shipping address for the buyer.
     *
     * @var \Iyzipay\Model\Address
     */
    protected $shippingAddress;

    /**
     * The billing address for the buyer.
     *
     * @var \Iyzipay\Model\Address
     */
    protected $billingAddress;

    /**
     * Basket items for single charge.
     *
     * @var array
     */
    protected $basketItems = [];

    /**
     * Total amount for charge.
     *
     * @var int
     */
    protected $price = 0;

    /**
     * The return url which will be triggered upon starting the single charge.
     *
     * @var string
     */
    protected $returnTo;

    /**
     * The return url which will be triggered upon starting the single charge.
     *
     * @param $returnTo
     * @return PerformsCharges
     */
    public function returnTo(string $returnTo)
    {
        $this->returnTo = $returnTo;

        return $this;
    }

    /**
     * Generate a checkout for a single charge on the customer for the given amount.
     */
    public function charge()
    {
        return $this->generateCheckoutForm();
    }

    /**
     * Generate a new checkout form.
     *
     * @return mixed
     */
    protected function generateCheckoutForm()
    {
        $request = new CreateCheckoutFormInitializeRequest();
        $request->setLocale(config('kasiyer.currency_locale'));
        $request->setConversationId($this->customer->id);
        $request->setPrice($this->price);
        $request->setPaidPrice($this->price);
        $request->setCurrency(config('kasiyer.currency'));
        $request->setPaymentGroup(PaymentGroup::PRODUCT);
        $request->setCallbackUrl($this->returnTo);
        $request->setEnabledInstallments(1);

        $request->setBuyer($this->buyer());
        $request->setShippingAddress($this->shippingAddress());
        $request->setBillingAddress($this->billingAddress());
        $request->setBasketItems($this->basketItems);

        return CheckoutFormInitialize::create($request, Kasiyer::iyzicoOptions())
            ->getCheckoutFormContent();
    }

    /**
     * Set buyer information.
     *
     * @return Buyer
     */
    protected function buyer()
    {
        $buyer = new Buyer();
        $buyer->setId($this->customer->id);
        $buyer->setName($this->customer->name);
        $buyer->setSurname($this->customer->surname);
        $buyer->setGsmNumber($this->customer->gsm_number);
        $buyer->setEmail($this->customer->iyzico_email);
        $buyer->setIdentityNumber($this->customer->identity_number);
        $buyer->setRegistrationAddress($this->customer->billing_address);
        $buyer->setIp($this->ipAddress());
        $buyer->setCity($this->customer->billing_city);
        $buyer->setCountry($this->customer->billing_country);
        $buyer->setZipCode($this->customer->billing_zip_code);

        return $this->buyer = $buyer;
    }

    /**
     * Set shipping address for the buyer.
     *
     * @return Address
     */
    protected function shippingAddress()
    {
        $shippingAddress = new Address();
        $shippingAddress->setContactName($this->customer->name . ' ' . $this->customer->surname);
        $shippingAddress->setCity($this->customer->shipping_city);
        $shippingAddress->setCountry($this->customer->shipping_country);
        $shippingAddress->setAddress($this->customer->shipping_address);
        $shippingAddress->setZipCode($this->customer->shipping_zip_code);

        return $this->shippingAddress = $shippingAddress;
    }

    /**
     * Set billing address for the buyer.
     *
     * @return Address
     */
    protected function billingAddress()
    {
        $billingAddress = new Address();
        $billingAddress->setContactName($this->customer->name . ' ' . $this->customer->surname);
        $billingAddress->setCity($this->customer->billing_city);
        $billingAddress->setCountry($this->customer->billing_country);
        $billingAddress->setAddress($this->customer->billing_address);
        $billingAddress->setZipCode($this->customer->billing_zip_code);

        return $this->billingAddress = $billingAddress;
    }

    /**
     * Set basket item for checkout.
     *
     * @param array $item
     * @return PerformsCharges
     */
    public function setBasketItem(array $item)
    {
        $basketItem = new BasketItem();
        $basketItem->setId($item['id']);
        $basketItem->setName($item['name']);
        $basketItem->setCategory1($item['category']);
        $basketItem->setItemType(BasketItemType::VIRTUAL);
        $basketItem->setPrice($item['price']);

        array_push($this->basketItems, $basketItem);

        $this->price += $item['price'];

        return $this;
    }

    /**
     * Get the ip address from client.
     *
     * @return mixed
     */
    protected function ipAddress()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }
}
