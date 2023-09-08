import {
    iPostC6RestResponse,
    restRequest,
    GET,
    POST,
    PUT,
    DELETE,
    iDeleteC6RestResponse,
    iGetC6RestResponse,
    iPutC6RestResponse,
    removeInvalidKeys
} from "@carbonorm/carbonnode";
import {deleteRestfulObjectArrays, updateRestfulObjectArrays} from "@carbonorm/carbonreact";
import {C6, iFeatures, features, RestShortTableNames} from "./C6";


export const Get = restRequest<{}, iFeatures, {}, iGetC6RestResponse<iFeatures>, RestShortTableNames>({
    C6: C6,
    tableName: features.TABLE_NAME,
    requestMethod: GET,
    queryCallback: (request) => {
        request.success ??= 'Successfully received features!'
        request.error ??= 'An unknown issue occurred creating the features!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iFeatures>(response?.data?.rest, "features", C6.features.PRIMARY_SHORT as (keyof iFeatures)[])
    }
})

export const Put = restRequest<{}, iFeatures, {}, iPutC6RestResponse<iFeatures>, RestShortTableNames>({
    C6: C6,
    tableName: features.TABLE_NAME,
    requestMethod: PUT,
    queryCallback: (request) => {
        request.success ??= 'Successfully updated features!'
        request.error ??= 'An unknown issue occurred updating the features!'
        return request
    },
    responseCallback: (response, request) => {
        updateRestfulObjectArrays<iFeatures>([
            removeInvalidKeys<iFeatures>({
                ...request,
                ...response?.data?.rest,
            }, C6.TABLES)
        ], "features", features.PRIMARY_SHORT as (keyof iFeatures)[])
    }
})


export const Post = restRequest<{}, iFeatures, {}, iPostC6RestResponse<iFeatures>, RestShortTableNames>({
    C6: C6,
    tableName: features.TABLE_NAME,
    requestMethod: POST,
    queryCallback: (request) => {
        request.success ??= 'Successfully created the features!'
        request.error ??= 'An unknown issue occurred creating the features!'
        return request
    },
    responseCallback: (response, request, id) => {
        if ('number' === typeof id || 'string' === typeof id) {
            if (1 !== features.PRIMARY_SHORT.length) {
                console.error("C6 received unexpected result's given the primary key length");
            } else {
                request[features.PRIMARY_SHORT[0]] = id
            }
        }
        updateRestfulObjectArrays<iFeatures>(
            undefined !== request.dataInsertMultipleRows
                ? request.dataInsertMultipleRows.map((request, index) => {
                    return removeInvalidKeys<iFeatures>({
                        ...request,
                        ...(index === 0 ? response?.data?.rest : {}),
                    }, C6.TABLES)
                })
                : [
                    removeInvalidKeys<iFeatures>({
                        ...request,
                        ...response?.data?.rest,
                    }, C6.TABLES)
                ]
            , "features", features.PRIMARY_SHORT as (keyof iFeatures)[])
    }
})

export const Delete = restRequest<{}, iFeatures, {}, iDeleteC6RestResponse<iFeatures>, RestShortTableNames>(
    {
        C6: C6,
        tableName: features.TABLE_NAME,
        requestMethod: DELETE,
        queryCallback: (request) => {
            request.success ??= 'Successfully removed the features!'
            request.error ??= 'An unknown issue occurred removing the features!'
            return request
        },
        responseCallback: (_response, request) => {
            // todo - request . where
            deleteRestfulObjectArrays<iFeatures>([
                request
            ], "features", features.PRIMARY_SHORT as (keyof iFeatures)[])
        }
    });


export default {
    Get, Post, Put, Delete
}
