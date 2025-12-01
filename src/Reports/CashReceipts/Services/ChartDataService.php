<?php

/**
 * ChartDataService - Prepare data for Chart.js visualizations
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\CashReceipts\Services;

use OpenEMR\Reports\CashReceipts\Model\ProviderSummary;

/**
 * Service for preparing Chart.js compatible data structures
 */
class ChartDataService
{
    /**
     * Colorblind-friendly palette (Okabe-Ito palette + extensions)
     */
    private const COLORS = [
        '#E69F00', // Orange
        '#56B4E9', // Sky Blue
        '#009E73', // Bluish Green
        '#F0E442', // Yellow
        '#0072B2', // Blue
        '#D55E00', // Vermillion
        '#CC79A7', // Reddish Purple
        '#999999', // Gray
        '#44AA99', // Teal
        '#882255', // Wine
        '#DDCC77', // Sand
        '#117733', // Green
    ];

    /**
     * Build provider revenue pie chart data
     *
     * @param ProviderSummary[] $providerSummaries
     * @return array Chart.js compatible structure
     */
    public function buildProviderRevenueChart(array $providerSummaries): array
    {
        $labels = [];
        $data = [];
        $backgroundColor = [];
        
        $colorIndex = 0;
        foreach ($providerSummaries as $summary) {
            $labels[] = $summary->getProviderName();
            $data[] = $summary->getGrandTotal();
            $backgroundColor[] = self::COLORS[$colorIndex % count(self::COLORS)];
            $colorIndex++;
        }

        return [
            'type' => 'pie',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'data' => $data,
                        'backgroundColor' => $backgroundColor,
                        'borderWidth' => 1,
                    ]
                ]
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => [
                        'position' => 'right',
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Revenue by Provider',
                    ],
                    'tooltip' => [
                        'callbacks' => [
                            'label' => 'formatCurrency',
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Build daily cash flow line chart data
     *
     * @param array $dailyCashFlow Array from MetricsService::getDailyCashFlow()
     * @return array Chart.js compatible structure
     */
    public function buildDailyCashFlowChart(array $dailyCashFlow): array
    {
        $labels = [];
        $totalData = [];
        $professionalData = [];
        $clinicData = [];

        foreach ($dailyCashFlow as $day) {
            $labels[] = $day['date'];
            $totalData[] = $day['total'];
            $professionalData[] = $day['professional'];
            $clinicData[] = $day['clinic'];
        }

        return [
            'type' => 'line',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Total',
                        'data' => $totalData,
                        'borderColor' => self::COLORS[0],
                        'backgroundColor' => $this->addAlpha(self::COLORS[0], 0.1),
                        'fill' => true,
                        'tension' => 0.4,
                    ],
                    [
                        'label' => 'Professional',
                        'data' => $professionalData,
                        'borderColor' => self::COLORS[1],
                        'backgroundColor' => $this->addAlpha(self::COLORS[1], 0.1),
                        'fill' => false,
                        'tension' => 0.4,
                        'borderDash' => [5, 5],
                    ],
                    [
                        'label' => 'Clinic',
                        'data' => $clinicData,
                        'borderColor' => self::COLORS[2],
                        'backgroundColor' => $this->addAlpha(self::COLORS[2], 0.1),
                        'fill' => false,
                        'tension' => 0.4,
                        'borderDash' => [5, 5],
                    ],
                ]
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'interaction' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
                'plugins' => [
                    'legend' => [
                        'position' => 'top',
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Daily Cash Flow',
                    ],
                    'tooltip' => [
                        'callbacks' => [
                            'label' => 'formatCurrency',
                        ]
                    ]
                ],
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'ticks' => [
                            'callback' => 'formatCurrency',
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Build top procedures bar chart data
     *
     * @param array $topProcedures Array from MetricsService::getTopProcedures()
     * @return array Chart.js compatible structure
     */
    public function buildTopProceduresChart(array $topProcedures): array
    {
        $labels = [];
        $data = [];

        foreach ($topProcedures as $procedure) {
            $label = $procedure['code'];
            if (!empty($procedure['code_type'])) {
                $label .= ' (' . $procedure['code_type'] . ')';
            }
            $labels[] = $label;
            $data[] = $procedure['total'];
        }

        return [
            'type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Revenue',
                        'data' => $data,
                        'backgroundColor' => self::COLORS[3],
                        'borderColor' => self::COLORS[3],
                        'borderWidth' => 1,
                    ]
                ]
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'indexAxis' => 'y',
                'plugins' => [
                    'legend' => [
                        'display' => false,
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Top Procedures by Revenue',
                    ],
                    'tooltip' => [
                        'callbacks' => [
                            'label' => 'formatCurrency',
                        ]
                    ]
                ],
                'scales' => [
                    'x' => [
                        'beginAtZero' => true,
                        'ticks' => [
                            'callback' => 'formatCurrency',
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Build payment method breakdown doughnut chart
     *
     * @param array $breakdown Array from MetricsService::getPaymentMethodBreakdown()
     * @return array Chart.js compatible structure
     */
    public function buildPaymentMethodChart(array $breakdown): array
    {
        return [
            'type' => 'doughnut',
            'data' => [
                'labels' => ['Copays', 'Insurance Payments'],
                'datasets' => [
                    [
                        'data' => [
                            $breakdown['copay']['total'],
                            $breakdown['ar_activity']['total']
                        ],
                        'backgroundColor' => [self::COLORS[4], self::COLORS[5]],
                        'borderWidth' => 1,
                    ]
                ]
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => [
                        'position' => 'bottom',
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Payment Method Breakdown',
                    ],
                    'tooltip' => [
                        'callbacks' => [
                            'label' => 'formatCurrencyWithPercentage',
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Add alpha channel to hex color
     *
     * @param string $hexColor Hex color code (e.g., '#FF0000')
     * @param float $alpha Alpha value 0-1
     * @return string RGBA color string
     */
    private function addAlpha(string $hexColor, float $alpha): string
    {
        $hex = ltrim($hexColor, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        return "rgba($r, $g, $b, $alpha)";
    }

    /**
     * Get color palette
     *
     * @return array
     */
    public function getColorPalette(): array
    {
        return self::COLORS;
    }
}
