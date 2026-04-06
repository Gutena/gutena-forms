import { __ } from '@wordpress/i18n';

const MCPConfigurationTemplate = ( props ) => {
    const { apiURL, username } = props;

    const listItems = [
        __( 'Create an Application Password.', 'gutena-forms' ),
        __( 'Copy the JSON config below into your AI client\'s MCP config file.', 'gutena-forms' ),
        __( 'Replace "your-application-password" with the password from Step 1.', 'gutena-forms' ),
        __( 'Add to .mcp.json (Claude Code), claude_desktop_config.json, or your client\'s MCP settings.', 'gutena-forms' ),
    ];

    return (
        <div className={ 'gutena-froms__mcp-configuration' }>
            <h6>{ __( 'Connect Your AI Client', 'gutena-forms' ) }</h6>

            <ol>
                { listItems.map( ( val, index ) => {

                    return (
                        <li key={ index }>{ val }</li>
                    );
                } ) }
            </ol>

            <pre>
                { `
{
\t"mcpServers": {
\t\t"gutenaForms": {
\t\t\t"command": "npx",
\t\t\t"args": [
\t\t\t\t"-y",
\t\t\t\t"@automattic/mcp-wordpress-remote@latest"
\t\t\t],
\t\t\t"env": {
\t\t\t\t"WP_API_URL": "${apiURL}",
\t\t\t\t"WP_API_USERNAME": "${username}",
\t\t\t\t"WP_API_PASSWORD": "your-application-password",
\t\t\t}
\t\t}
\t}
}
                ` }
            </pre>
        </div>
    );
}

export default MCPConfigurationTemplate;