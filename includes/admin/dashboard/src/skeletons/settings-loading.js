import Skeleton from "react-loading-skeleton";

const SettingsLoading = ( { hasTitle = true } ) => {

    return (
        <div>
            { hasTitle && (
                <>
                    <div
                        style={ { marginBottom: '10px' } }
                    >
                        <Skeleton width={ 185 } height={ 24 } />
                    </div>
                    <div>
                        <Skeleton width={ 680 } height={ 12 } />
                        <Skeleton width={ 300 } height={ 12 } />
                    </div>
                </>
            ) }

            <div
                style={ { padding: '20px 25px', background: '#fff', width: '600px' } }
            >

                <div style={ { marginBottom: '15px' } }>
                    <div style={ { display: 'inline-block', marginRight: '10px' } }>
                        <Skeleton borderRadius={ 50 } width={ 35 } height={ 20 } />
                    </div>
                    <div style={ { display: 'inline-block' } }>
                        <Skeleton width={ 255 } height={ 20 } />
                    </div>
                </div>

                <div
                    style={ { marginBottom: '15px' } }
                >
                    <Skeleton width={ 55 } height={ 12 } />
                    <div
                        style={ { borderRadius: '4px', padding: '10px 15px', border: '1px solid #e2e2e2' } }
                    >
                        <Skeleton width={ 170 } height={ 10 } />
                    </div>
                    <Skeleton width={ 335 } height={ 12 } />

                </div>

                <div>
                    <Skeleton width={ 100 } height={ 30 } />
                </div>
            </div>
        </div>
    );
};

export default SettingsLoading;