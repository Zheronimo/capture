<?php
/*
 * This template is used for the display of 404 (Not Found) errors.
 */

get_header(); ?>
	<section class="content-wrapper 404-content cf">
		<article class="content cf">
			<section class="404-error no-posts post cf">
				<section class="post-container">
					<section class="post-title-wrap cf">
						<h1 title="404 Error" class="page-title">Ошибка 404</h1>
					</section>

					<article class="post-content cf">
						<p>По данному адресу ничего не найдено. Воспользуйтесь поиском.</p>

						<section id="search-again" class="search-again search-block no-posts no-search-results">
							<p>Поиск:</p>
							<?php echo get_search_form(); ?>
						</section>

						<?php sds_sitemap(); ?>
					</article>
				</section>
			</section>
		</article>
	</section>

<?php get_footer(); ?>