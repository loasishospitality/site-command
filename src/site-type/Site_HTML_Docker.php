<?php

namespace EE\Site\Type;

use function \EE\Utils\mustache_render;

class Site_HTML_Docker {

	/**
	 * Generate docker-compose.yml according to requirement.
	 *
	 * @param array $filters Array to determine the docker-compose.yml generation.
	 *
	 * @return String docker-compose.yml content string.
	 */
	public function generate_docker_compose_yml( array $filters = [] ) {
		$img_versions = \EE\Utils\get_image_versions();
		$base         = [];

		$restart_default = [ 'name' => 'always' ];
		$network_default = [
			'net' => [
				[ 'name' => 'site-network' ]
			]
		];

		// nginx configuration.
		$nginx['service_name'] = [ 'name' => 'nginx' ];
		$nginx['image']        = [ 'name' => 'easyengine/nginx:' . $img_versions['easyengine/nginx'] ];
		$nginx['restart']      = $restart_default;

		$v_host = 'VIRTUAL_HOST';

		$nginx['environment'] = [
			'env' => [
				[ 'name' => $v_host ],
				[ 'name' => 'VIRTUAL_PATH=/' ],
				[ 'name' => 'HSTS=off' ],
			],
		];
		$nginx['volumes']     = [
			'vol' => [
				[ 'name' => './app/src:/var/www/htdocs' ],
				[ 'name' => './config/nginx/default.conf:/etc/nginx/conf.d/default.conf' ],
				[ 'name' => './logs/nginx:/var/log/nginx' ],
				[ 'name' => './config/nginx/common:/usr/local/openresty/nginx/conf/common' ],
			],
		];
		$nginx['labels']      = [
			'label' => [
				'name' => 'io.easyengine.site=${VIRTUAL_HOST}',
			],
		];
		$nginx['networks']    = [
			'net' => [
				[ 'name' => 'site-network' ],
				[ 'name' => 'global-network' ],
			]
		];

		$base[] = $nginx;

		$binding = [
			'services' => $base,
			'network'  => [
				'networks_labels' => [
					'label' => [
						[ 'name' => 'org.label-schema.vendor=EasyEngine' ],
						[ 'name' => 'io.easyengine.site=${VIRTUAL_HOST}' ],
					],
				],
			],
		];

		$docker_compose_yml = mustache_render( SITE_TEMPLATE_ROOT . '/docker-compose.mustache', $binding );

		return $docker_compose_yml;
	}
}
