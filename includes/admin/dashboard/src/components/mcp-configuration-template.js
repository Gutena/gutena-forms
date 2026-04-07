import { __ } from '@wordpress/i18n';
import Copy from "../icons/copy";
import { toast } from "react-toastify";

const MCPConfigurationTemplate = ( props ) => {
    const { apiURL, username } = props;

    const listItems = [
        __( 'Create an Application Password.', 'gutena-forms' ),
        __( 'Copy the JSON config below into your AI client\'s MCP config file.', 'gutena-forms' ),
        __( 'Replace "your-application-password" with the password from Step 1.', 'gutena-forms' ),
        __( 'Add to .mcp.json (Claude Code), claude_desktop_config.json, or your client\'s MCP settings.', 'gutena-forms' ),
    ];

    const selectAndCopyCodeToClipboard = () => {
        const codeElement = document.getElementById( 'gutena-forms__mcp-configuration-code' );
        const range = document.createRange();
        range.selectNodeContents( codeElement );
        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange( range );

        try {
            const successful = document.execCommand( 'copy' );
            if ( successful ) {
                toast.success( __( 'Configuration code copied to clipboard!', 'gutena-forms' ) );
            }
        } catch {
        }
    };

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
                <div
                    className={ 'gutena-forms__copy-icon' }
                    onClick={ selectAndCopyCodeToClipboard }
                >
                    <Copy />
                </div>
                <div id={ 'gutena-forms__mcp-configuration-code' }>
                    { `{
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
}` }
                </div>
            </pre>
        </div>
    );
}

export default MCPConfigurationTemplate;