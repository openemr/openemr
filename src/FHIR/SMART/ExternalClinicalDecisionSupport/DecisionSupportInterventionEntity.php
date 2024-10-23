<?php

namespace OpenEMR\FHIR\SMART\ExternalClinicalDecisionSupport;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;

abstract class DecisionSupportInterventionEntity
{
    protected ?ClientEntity $client;
    protected string $id;
    protected string $name;
    protected string $type;
    protected array $fields;
    protected array $fieldsByIndex;

    protected array $allowedTypes = ['text' => true];

    public function __construct(string $type, ?ClientEntity $client = null)
    {
        $this->setType($type);
        if ($client != null) {
            $this->setClient($client);
        }
        $this->fields = [];
        $this->fieldsByIndex = [];
    }

    public function setClient(ClientEntity $client)
    {
        $this->client = $client;
        $this->id = $client->getIdentifier();
        $this->name = $client->getName();
    }

    /**
     * @return ClientEntity
     */
    public function getClient(): ClientEntity
    {
        return $this->client;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getFields(): array
    {
        return array_map(function ($name) {
            return $this->fields[$name];
        }, $this->fieldsByIndex);
    }

    public function setField(string $name, string $label, string $value, string $type = "text", array $options = []): void
    {
        $type = isset($this->allowedTypes[$type]) ? $type : 'text';
        $this->fields[$name] = ['name' => $name, 'label' => $label, 'value' => $value, 'type' => $type, 'options' => $options];
        $this->fieldsByIndex[] = $name;
    }

    public function setFieldValue(string $name, string $value): void
    {
        if (!array_key_exists($name, $this->fields)) {
            throw new \InvalidArgumentException("Field does not exist");
        }
        $this->fields[$name]['value'] = $value;
    }

    public function setType(string $type): void
    {
        if (!in_array($type, ['predictive', 'evidence-based'])) {
            throw new \InvalidArgumentException("Invalid type");
        }
        $this->type = $type;
    }

    protected function populateServiceWithFhirQuestionnaireForType(string $serviceType, string $questionnaire, string $response = null)
    {

        $questionnaire = json_decode($questionnaire, true);
        foreach ($questionnaire['item'] as $item) {
            if ($item['linkId'] === $serviceType) {
                $this->createFieldsFromQuestionnaireItem($item);
            }
        }
        if (!empty($response)) {
            $this->populateFromQuestionnaireResponseForType($serviceType, $response);
        }
    }
    public function createFieldsFromQuestionnaireItem($item)
    {
        if ($item['type'] == 'group') {
            foreach ($item['item'] as $field) {
                $this->createFieldsFromQuestionnaireItem($field);
            }
        } else {
            $this->setField($item['linkId'], $item['text'], '');
        }
    }

    protected function populateFromQuestionnaireResponseForType(string $serviceType, string $response)
    {
        $response = json_decode($response, true);
        foreach ($response['item'] as $item) {
            if ($item['linkId'] === $serviceType) {
                foreach ($item['item'] as $field) {
                    $this->setFieldFromQuestionnaireResponseItem($field);
                }
            }
        }
    }

    private function setFieldFromQuestionnaireResponseItem($item)
    {
        if (!empty($item['item'])) {
            foreach ($item['item'] as $field) {
                $this->setFieldFromQuestionnaireResponseItem($field);
            }
        }
        if (!empty($item['answer']) && $this->hasField($item['linkId'])) {
            $this->setFieldValue($item['linkId'], $item['answer'][0]['valueString']);
        }
    }

    public function hasField($linkId)
    {
        return array_key_exists($linkId, $this->fields);
    }
}
