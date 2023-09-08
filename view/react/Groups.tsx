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
import {C6, iGroups, groups, RestShortTableNames} from "./C6";


export const Get = restRequest<{}, iGroups, {}, iGetC6RestResponse<iGroups>, RestShortTableNames>({
    C6: C6,
    tableName: groups.TABLE_NAME,
    requestMethod: GET,
    queryCallback: (request) => {
        request.success ??= 'Successfully received groups!'
        request.error ??= 'An unknown issue occurred creating the groups!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iGroups>(response?.data?.rest, "groups", C6.groups.PRIMARY_SHORT as (keyof iGroups)[])
    }
})

export const Put = restRequest<{}, iGroups, {}, iPutC6RestResponse<iGroups>, RestShortTableNames>({
    C6: C6,
    tableName: groups.TABLE_NAME,
    requestMethod: PUT,
    queryCallback: (request) => {
        request.success ??= 'Successfully updated groups!'
        request.error ??= 'An unknown issue occurred updating the groups!'
        return request
    },
    responseCallback: (response, request) => {
        updateRestfulObjectArrays<iGroups>([
            removeInvalidKeys<iGroups>({
                ...request,
                ...response?.data?.rest,
            }, C6.TABLES)
        ], "groups", groups.PRIMARY_SHORT as (keyof iGroups)[])
    }
})


export const Post = restRequest<{}, iGroups, {}, iPostC6RestResponse<iGroups>, RestShortTableNames>({
    C6: C6,
    tableName: groups.TABLE_NAME,
    requestMethod: POST,
    queryCallback: (request) => {
        request.success ??= 'Successfully created the groups!'
        request.error ??= 'An unknown issue occurred creating the groups!'
        return request
    },
    responseCallback: (response, request, id) => {
        if ('number' === typeof id || 'string' === typeof id) {
            if (1 !== groups.PRIMARY_SHORT.length) {
                console.error("C6 received unexpected result's given the primary key length");
            } else {
                request[groups.PRIMARY_SHORT[0]] = id
            }
        }
        updateRestfulObjectArrays<iGroups>(
            undefined !== request.dataInsertMultipleRows
                ? request.dataInsertMultipleRows.map((request, index) => {
                    return removeInvalidKeys<iGroups>({
                        ...request,
                        ...(index === 0 ? response?.data?.rest : {}),
                    }, C6.TABLES)
                })
                : [
                    removeInvalidKeys<iGroups>({
                        ...request,
                        ...response?.data?.rest,
                    }, C6.TABLES)
                ]
            , "groups", groups.PRIMARY_SHORT as (keyof iGroups)[])
    }
})

export const Delete = restRequest<{}, iGroups, {}, iDeleteC6RestResponse<iGroups>, RestShortTableNames>(
    {
        C6: C6,
        tableName: groups.TABLE_NAME,
        requestMethod: DELETE,
        queryCallback: (request) => {
            request.success ??= 'Successfully removed the groups!'
            request.error ??= 'An unknown issue occurred removing the groups!'
            return request
        },
        responseCallback: (_response, request) => {
            // todo - request . where
            deleteRestfulObjectArrays<iGroups>([
                request
            ], "groups", groups.PRIMARY_SHORT as (keyof iGroups)[])
        }
    });


export default {
    Get, Post, Put, Delete
}
