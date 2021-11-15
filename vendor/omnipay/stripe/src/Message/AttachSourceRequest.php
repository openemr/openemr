<?php
/**
 * CreateSourceRequest
 */
namespace Omnipay\Stripe\Message;

/**
 * Class CreateSourceRequest
 *
 * TODO : Add docblock
 */
class AttachSourceRequest extends AbstractRequest
{
    /**
     * @return mixed
     */
    public function getData()
    {
        $this->validate('customerReference', 'source');

        $data['source'] = $this->getSource();

        return $data;
    }

    /**
     * @inheritdoc
     *
     * @return string The endpoint for the create token request.
     */
    public function getEndpoint()
    {
        return $this->endpoint . '/customers/' . $this->getCustomerReference() . '/sources';
    }
}
