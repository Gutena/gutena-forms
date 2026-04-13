import { __ } from '@wordpress/i18n';
import {Button} from "@wordpress/components";
import Download from '../icons/download';

const GutenaFormsMcp = () => {

    return (
        <div className={ 'gutena-forms__mcp-wrapper' }>
            <p>
                { __( 'To connect AI clients to your forms, you need to install the MCP Adapter plugin. Follow the steps below to download and activate it before configuring the settings shown above.', 'gutena-forms' ) }
            </p>

            <ol>
                <li>
                    { __( 'Download the latest release from', 'gutena-forms' ) }
                    &nbsp;
                    <a
                        href={ 'https://github.com/wordpress/mcp-adapter' }
                        target={ '_blank' }
                    >{ __( 'GitHub', 'gutena-forms' ) }</a>.
                </li>
                <li>{ __( 'Install the plugin via Plugins > Add New> Upload Plugin.', 'gutena-forms' ) }</li>
                <li>{ __( 'Activate the MCP Adapter plugin.', 'gutena-forms' ) }</li>
            </ol>

            <Button
                className={ 'gutena-forms__primary-button' }
                href={ 'https://github.com/WordPress/mcp-adapter/releases' }
                target={ '_blank' }
            >
                <Download />
                { __( 'Download MCP Adapter' ) }
            </Button>
        </div>
    );
};

export default GutenaFormsMcp;