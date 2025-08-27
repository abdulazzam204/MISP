<?php
// Load Chart.js from MISP's asset loader
echo $this->element('genericElements/assetLoader', [
    'js' => ['Chart.min']
]);

$randomNumber = rand();
$attackPatterns = $data['data'];
$labels = json_encode(array_keys($attackPatterns));
$values = json_encode(array_values($attackPatterns));
?>

<div class="dashboard-widget-content" style="padding:10px; height:100%; width:100%;">
    <canvas id="attackPatternChart-<?php echo $randomNumber; ?>" style="height:100%; width:100%;"></canvas>
</div>

<script>
$(document).ready(function () {
    const chartId = 'attackPatternChart-<?php echo $randomNumber; ?>';
    const ctx = document.getElementById(chartId).getContext('2d');

    // Initialize chart
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
            maintainAspectRatio: false, // allow full height/width scaling
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 20,
                        color: '#000000',
                        font: {
                            family: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif",
                            size: 13,
                            lineHeight: 1.3
                        },
                    },
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            let value = context.raw || 0;
                            return label + ': ' + value + ' events';
                        }
                    }
                }
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
