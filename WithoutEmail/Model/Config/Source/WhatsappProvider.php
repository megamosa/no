<?php
/**
 * MagoArab_WithoutEmail extension
 *
 * @category  MagoArab
 * @package   MagoArab_WithoutEmail
 * @author    MagoArab
 */
declare(strict_types=1);

namespace MagoArab\WithoutEmail\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class WhatsappProvider implements OptionSourceInterface
{
    /**
     * Provider options
     */
    const PROVIDER_ULTRAMSG = 'ultramsg';
    const PROVIDER_DIALOG360 = 'dialog360';

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::PROVIDER_ULTRAMSG,
                'label' => __('UltraMsg')
            ],
            [
                'value' => self::PROVIDER_DIALOG360,
                'label' => __('360Dialog')
            ]
        ];
    }
}