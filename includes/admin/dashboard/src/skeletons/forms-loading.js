import Skeleton from "react-loading-skeleton";

const FormsLoading = () => {

    const thStyles = { height: '40px', background: '#f9f9f9', padding: '0, 15px' };
    const tdStyles = { height: '40px', background: '#fff' };

    return (
        <div>
            <div
                style={ { marginBottom: '30px' } }
            >
                <div
                    style={ { display: 'inline-block', marginRight: '20px' } }
                >
                    <Skeleton width={ 180 } height={ 30 } />
                </div>
                <div
                    style={ { display: 'inline-block', marginRight: '20px' } }
                >
                    <Skeleton width={ 120 } height={ 30 } />
                </div>
            </div>

            <div
                style={ { borderRadius: '10px', border: '1px solid #e2e2e2', background: '#fff', padding: '20px' } }
            >

                <div
                    style={ { marginBottom: '20px' } }
                >
                    <div
                        style={ { display: 'inline-block', marginRight: '4px' } }
                    >
                        <Skeleton width={ 50 } height={ 25 } />
                    </div>
                    <div
                        style={ { display: 'inline-block', marginRight: '4px' } }
                    >
                        <Skeleton width={ 100 } height={ 25 } />
                    </div>
                </div>

                <table
                    style={ { border: '0.5px solid #d4d4e4', borderRadius: '4px', background: '#f9f9f9', width: '100%' } }
                    cellSpacing={ 0 }
                    cellPadding={ 0 }
                >
                    <thead>
                        <tr>
                            <td
                                style={ thStyles }
                            >
                                <Skeleton width={ 12 } height={ 12 } />
                            </td>
                            <td
                                style={ thStyles }
                            >
                                <Skeleton width={ 90 } height={ 15 } />
                            </td>
                            <td
                                style={ thStyles }
                            >
                                <Skeleton width={ 55 } height={ 15 } />
                            </td>
                            <td
                                style={ thStyles }
                            >
                                <Skeleton width={ 85 } height={ 15 } />
                            </td>
                            <td
                                style={ thStyles }
                            >
                                <Skeleton width={ 60 } height={ 15 } />
                            </td>
                        </tr>
                    </thead>

                    <tbody>
                    { Array.from( { length: 4 } ).map( () => {

                        return (
                            <tr>
                                <td
                                    style={ tdStyles }
                                >
                                    <Skeleton width={ 12 } height={ 12 } />
                                </td>
                                <td
                                    style={ tdStyles }
                                >
                                    <Skeleton width={ 90 } height={ 15 } />
                                </td>
                                <td
                                    style={ tdStyles }
                                >
                                    <Skeleton width={ 55 } height={ 15 } />
                                </td>
                                <td
                                    style={ tdStyles }
                                >
                                    <Skeleton width={ 85 } height={ 15 } />
                                </td>
                                <td
                                    style={ tdStyles }
                                >
                                    <Skeleton width={ 60 } height={ 15 } />
                                </td>
                            </tr>
                        );
                    } ) }
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colSpan={ 2 } style={ thStyles }>
                                <div style={ { display: 'inline-block', marginRight: '10px' } }>
                                    <Skeleton width={ 55 } height={ 15 } />
                                </div>

                                <div style={ { display: 'inline-block' } }>
                                    <Skeleton width={ 20 } height={ 15 } />
                                </div>
                            </td>
                            <td colSpan={ 3 } style={ thStyles }>
                                <div style={ { float: 'right' } }>
                                    <Skeleton width={ 60 } height={ 20 } />
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>

            </div>
        </div>
    );
};

export default FormsLoading;