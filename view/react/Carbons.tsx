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
import {C6, iCarbons, carbons, RestShortTableNames} from "./C6";


export const Get = restRequest<{}, iCarbons, {}, iGetC6RestResponse<iCarbons>, RestShortTableNames>({
    C6: C6,
    tableName: carbons.TABLE_NAME,
    requestMethod: GET,
    queryCallback: (request) => {
        request.success ??= 'Successfully received carbons!'
        request.error ??= 'An unknown issue occurred creating the carbons!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iCarbons>(response?.data?.rest, "carbons", C6.carbons.PRIMARY_SHORT as (keyof iCarbons)[])
    }
})

export const Put = restRequest<{}, iCarbons, {}, iPutC6RestResponse<iCarbons>, RestShortTableNames>({
    C6: C6,
    tableName: carbons.TABLE_NAME,
    requestMethod: PUT,
    queryCallback: (request) => {
        request.success ??= 'Successfully updated carbons!'
        request.error ??= 'An unknown issue occurred updating the carbons!'
        return request
    },
    responseCallback: (response, request) => {
        updateRestfulObjectArrays<iCarbons>([
            removeInvalidKeys<iCarbons>({
                ...request,
                ...response?.data?.rest,
            }, C6.TABLES)
        ], "carbons", carbons.PRIMARY_SHORT as (keyof iCarbons)[])
    }
})


export const Post = restRequest<{}, iCarbons, {}, iPostC6RestResponse<iCarbons>, RestShortTableNames>({
    C6: C6,
    tableName: carbons.TABLE_NAME,
    requestMethod: POST,
    queryCallback: (request) => {
        request.success ??= 'Successfully created the carbons!'
        request.error ??= 'An unknown issue occurred creating the carbons!'
        return request
    },
    responseCallback: (response, request, id) => {
        if ('number' === typeof id || 'string' === typeof id) {
            if (1 !== carbons.PRIMARY_SHORT.length) {
                console.error("C6 received unexpected result's given the primary key length");
            } else {
                request[carbons.PRIMARY_SHORT[0]] = id
            }
        }
        updateRestfulObjectArrays<iCarbons>(
            undefined !== request.dataInsertMultipleRows
                ? request.dataInsertMultipleRows.map((request, index) => {
                    return removeInvalidKeys<iCarbons>({
                        ...request,
                        ...(index === 0 ? response?.data?.rest : {}),
                    }, C6.TABLES)
                })
                : [
                    removeInvalidKeys<iCarbons>({
                        ...request,
                        ...response?.data?.rest,
                    }, C6.TABLES)
                ]
            , "carbons", carbons.PRIMARY_SHORT as (keyof iCarbons)[])
    }
})

export const Delete = restRequest<{}, iCarbons, {}, iDeleteC6RestResponse<iCarbons>, RestShortTableNames>(
    {
        C6: C6,
        tableName: carbons.TABLE_NAME,
        requestMethod: DELETE,
        queryCallback: (request) => {
            request.success ??= 'Successfully removed the carbons!'
            request.error ??= 'An unknown issue occurred removing the carbons!'
            return request
        },
        responseCallback: (_response, request) => {
            // todo - request . where
            deleteRestfulObjectArrays<iCarbons>([
                request
            ], "carbons", carbons.PRIMARY_SHORT as (keyof iCarbons)[])
        }
    });


export default {
    Get, Post, Put, Delete
}
