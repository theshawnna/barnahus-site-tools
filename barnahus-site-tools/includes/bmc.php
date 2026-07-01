<?php

// Show Buy Me a Coffee only on selected pages.
\add_action('wp_footer', function () {
	?>
	<script>
	(function () {

		const allowedBmcPages = [
			'/about/who-we-are/',
			'/about/vision/',
			'/category/news/',
			'/about/milestones/',
			'/about/contact-us/',
			'/barnahus/about-barnahus/',
			'/barnahus/the-setup-of-barnahus/where-to-start/',
			'/barnahus/the-practice-in-barnahus/standards/',
			'/barnahus/the-practice-in-barnahus/the-multidisciplinary-team/',
			'/barnahus/the-practice-in-barnahus/progress-in-europe/',
			'/membership/current-members/',
			'/library/'
		];

		const currentPath = window.location.pathname.replace(/\/+$/, '/') || '/';

		function hideBmc() {
			document.querySelectorAll(
				'#bmc-wbtn, #bmc-iframe, #bmc-wbtn + div, #WidgetFloaterPanels'
			).forEach(function (el) {
				el.style.setProperty('display', 'none', 'important');
				el.style.setProperty('visibility', 'hidden', 'important');
				el.style.setProperty('opacity', '0', 'important');
				el.style.setProperty('pointer-events', 'none', 'important');
			});
		}

		function controlBmc() {
			if (!allowedBmcPages.includes(currentPath)) {
				hideBmc();
			}
		}

		controlBmc();

		new MutationObserver(controlBmc).observe(document.body, {
			childList: true,
			subtree: true
		});

	})();
	</script>
	<?php
});

// Show Buy Me a Coffee only on selected pages and style it.
\add_action('wp_head', function () {
	?>
	<script>
	(function () {
		function styleBmc() {
			var bmcButton = document.querySelector('#bmc-wbtn');

			if (bmcButton) {
				bmcButton.style.setProperty('background', '#dce0f7', 'important');
				bmcButton.style.setProperty('background-color', '#dce0f7', 'important');
				bmcButton.style.setProperty('font-family', 'PT Serif', 'important');
			}

			document.querySelectorAll('#bmc-wbtn *, #bmc-wbtn + div, #bmc-wbtn + div *')
				.forEach(function (el) {
					el.style.setProperty('font-family', 'PT Serif', 'important');
				});
		}

		styleBmc();
		setInterval(styleBmc, 500);
	})();
	</script>
	<?php
});


// Force Buy Me a Coffee styling immediately.
add_action('wp_head', function () {
	?>
	<style>
		#bmc-wbtn {
			background: #dce0f7 !important;
			background-color: #dce0f7 !important;
		}

		#bmc-wbtn,
		#bmc-wbtn *,
		#bmc-wbtn + div,
		#bmc-wbtn + div * {
			font-family: "PT Serif", serif !important;
		}

		/* Buy Me a Coffee popup */
		#bmc-wbtn + div {
			font-size: 16px !important;
			line-height: 1.45 !important;
		}
	</style>
	<?php
}, 1);

