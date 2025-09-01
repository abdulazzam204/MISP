<?php
// Load Chart.js from MISP's asset loader
echo $this->element('genericElements/assetLoader', [
    'js' => ['Chart.min']
]);

$randomNumber = rand();
$attackPatterns = $data['data'];
$labels = json_encode(array_keys($attackPatterns));
$values = json_encode(array_values($attackPatterns));
if (isset($data['links'])) {
    $links = json_encode($data['links']);
}
?>

<div class="dashboard-widget-content" style="padding:10px; height:100%; width:100%;">
    <canvas id="attackPatternChart-<?php echo $randomNumber; ?>" style="height:100%; width:100%;"></canvas>
</div>

<script>
    $(document).ready(function () {
        const chartId = 'attackPatternChart-<?php echo $randomNumber; ?>';
        const ctx = document.getElementById(chartId).getContext('2d');
        Chart.defaults.font.family = "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif";
        Chart.defaults.color = '#000000';
        const urls = <?= $links ?>;

        const chartInstance = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: <?php echo $labels; ?>,
                datasets: [{
                    //label: 'Attack Pattern Distribution',
                    data: <?php echo $values; ?>,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56',
                        '#4BC0C0', '#9966FF', '#FF9F40'
                    ],
                    hoverOffset: 5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                onClick: (evt, elements) => {
                    if (elements.length > 0) {
                        const chartElement = elements[0];
                        const label = chartInstance.data.labels[chartElement.index];
                        if (urls[label]) {
                            window.location.href = urls[label];
                        } else {
                            alert('No events found for ' + label);
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 20,
                        },
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                let label = context.label || '';
                                let value = context.raw || 0;
                                return label + ': ' + value + ' events';
                            }
                        }
                    },
                }
            }
        });

        // Add ResizeObserver for smooth resizing when widget size changes
        const container = document.getElementById(chartId).parentElement;
        if (window.ResizeObserver) {
            const resizeObserver = new ResizeObserver(() => {
                chartInstance.resize();
            });
            resizeObserver.observe(container);
        } else {
            // fallback for older browsers
            $(window).on('resize', () => chartInstance.resize());
        }
    });
</script>