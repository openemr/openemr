<?php

namespace GeneralDermatology\Services\Info;

use GeneralDermatology\Services\Info\Processors\LaserInfoProcessor;
use GeneralDermatology\Services\Info\Processors\BiopsyInfoProcessor;
use GeneralDermatology\Services\Info\Processors\CryotherapyInfoProcessor;
use GeneralDermatology\Services\Info\Processors\CanThardinInfoProcessor;
use GeneralDermatology\Services\Info\Processors\IncisionInfoProcessor;
use GeneralDermatology\Services\Info\Processors\ElectroInfoProcessor;
use GeneralDermatology\Services\Info\Processors\DebridementInfoProcessor;
use GeneralDermatology\Services\Info\Processors\DermabrasionInfoProcessor;
use GeneralDermatology\Services\Info\Processors\ExtractionInfoProcessor;
use GeneralDermatology\Services\Info\Processors\HyfrecationInfoProcessor;
use GeneralDermatology\Services\Info\Processors\IntralesionalInfoProcessor;
use GeneralDermatology\Services\Info\Processors\FillerInfoProcessor;
use GeneralDermatology\Services\Info\Processors\SclerotherapyInfoProcessor;
use GeneralDermatology\Services\Info\Processors\PhotodynamicInfoProcessor;
use GeneralDermatology\Services\Info\Processors\PlateletInfoProcessor;
use GeneralDermatology\Services\Info\Processors\MicroneedlingInfoProcessor;
use GeneralDermatology\Services\Info\Processors\NeuromodulatorInfoProcessor;
use GeneralDermatology\Services\Info\Processors\NonlaserInfoProcessor;
use GeneralDermatology\Services\Info\Processors\PeelInfoProcessor;
use GeneralDermatology\Services\Info\Processors\SilverInfoProcessor;
use GeneralDermatology\Services\Info\Processors\SnipInfoProcessor;
use GeneralDermatology\Services\Info\Processors\UnnaBootInfoProcessor;
use GeneralDermatology\Services\Info\Processors\GenericProcedureInfoProcessor;

/**
 * ProcedureInfoProcessorFactory
 *
 * Factory class that instantiates the correct procedure processor based on the procedure type.
 * Uses match() expression (PHP 8.0+) for clean, no-conditional instantiation.
 *
 * This factory eliminates if/else or switch statements from the calling code.
 * The service just calls Factory::create($type) and gets back the right processor.
 *
 * Usage:
 * $processor = ProcedureInfoProcessorFactory::create('Laser');
 * $fields = $processor->extractFields($treatment);
 * $html = $processor->formatForDisplay($fields);
 */
class ProcedureInfoProcessorFactory
{
    /**
     * Create and return the appropriate processor for the given procedure type
     *
     * @param string $procedureType The procedure type (e.g., 'Laser', 'Biopsy', 'Cryotherapy')
     * @return ProcedureInfoProcessor The processor instance implementing the interface
     */
    public static function create(string $procedureType): ProcedureInfoProcessor
    {
        return match($procedureType) {
            'Laser' => new LaserInfoProcessor(),
            'Biopsy' => new BiopsyInfoProcessor(),
            'Cryotherapy' => new CryotherapyInfoProcessor(),
            'Cantharidin' => new CanThardinInfoProcessor(),
            'Incision' => new IncisionInfoProcessor(),
            'Electro' => new ElectroInfoProcessor(),
            'Debridement' => new DebridementInfoProcessor(),
            'Dermabrasion' => new DermabrasionInfoProcessor(),
            'Extraction' => new ExtractionInfoProcessor(),
            'Hyfrecation' => new HyfrecationInfoProcessor(),
            'Intralesional' => new IntralesionalInfoProcessor(),
            'Filler' => new FillerInfoProcessor(),
            'Sclerotherapy' => new SclerotherapyInfoProcessor(),
            'Photodynamic' => new PhotodynamicInfoProcessor(),
            'Platelet' => new PlateletInfoProcessor(),
            'Microneedling' => new MicroneedlingInfoProcessor(),
            'Neuromodulator' => new NeuromodulatorInfoProcessor(),
            'NonLaser' => new NonlaserInfoProcessor(),
            'Peel' => new PeelInfoProcessor(),
            'Silver' => new SilverInfoProcessor(),
            'Snip' => new SnipInfoProcessor(),
            'Unna' => new UnnaBootInfoProcessor(),
            default => new GenericProcedureInfoProcessor(),
        };
    }
}
