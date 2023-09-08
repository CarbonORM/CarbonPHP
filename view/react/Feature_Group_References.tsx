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
import {C6, iFeature_Group_References, feature_group_references, RestShortTableNames} from "./C6";


export const Get = restRequest<{}, iFeature_Group_References, {}, iGetC6RestResponse<iFeature_Group_References>, RestShortTableNames>({
    C6: C6,
    tableName: feature_group_references.TABLE_NAME,
    requestMethod: GET,
    queryCallback: (request) => {
        request.success ??= 'Successfully received feature group references!'
        request.error ??= 'An unknown issue occurred creating the feature group references!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iFeature_Group_References>(response?.data?.rest, "feature_group_references", C6.feature_group_references.PRIMARY_SHORT as (keyof iFeature_Group_References)[])
    }
})

export const Put = restRequest<{}, iFeature_Group_References, {}, iPutC6RestResponse<iFeature_Group_References>, RestShortTableNames>({
    C6: C6,
    tableName: feature_group_references.TABLE_NAME,
    requestMethod: PUT,
    queryCallback: (request) => {
        request.success ??= 'Successfully updated feature group references!'
        request.error ??= 'An unknown issue occurred updating the feature group references!'
        return request
    },
    responseCallback: (response, request) => {
        updateRestfulObjectArrays<iFeature_Group_References>([
            removeInvalidKeys<iFeature_Group_References>({
                ...request,
                ...response?.data?.rest,
            }, C6.TABLES)
        ], "feature_group_references", feature_group_references.PRIMARY_SHORT as (keyof iFeature_Group_References)[])
    }
})


export const Post = restRequest<{}, iFeature_Group_References, {}, iPostC6RestResponse<iFeature_Group_References>, RestShortTableNames>({
    C6: C6,
    tableName: feature_group_references.TABLE_NAME,
    requestMethod: POST,
    queryCallback: (request) => {
        request.success ??= 'Successfully created the feature group references!'
        request.error ??= 'An unknown issue occurred creating the feature group references!'
        return request
    },
    responseCallback: (response, request, id) => {
        if ('number' === typeof id || 'string' === typeof id) {
            if (1 !== feature_group_references.PRIMARY_SHORT.length) {
                console.error("C6 received unexpected result's given the primary key length");
            } else {
                request[feature_group_references.PRIMARY_SHORT[0]] = id
            }
        }
        updateRestfulObjectArrays<iFeature_Group_References>(
            undefined !== request.dataInsertMultipleRows
                ? request.dataInsertMultipleRows.map((request, index) => {
                    return removeInvalidKeys<iFeature_Group_References>({
                        ...request,
                        ...(index === 0 ? response?.data?.rest : {}),
                    }, C6.TABLES)
                })
                : [
                    removeInvalidKeys<iFeature_Group_References>({
                        ...request,
                        ...response?.data?.rest,
                    }, C6.TABLES)
                ]
            , "feature_group_references", feature_group_references.PRIMARY_SHORT as (keyof iFeature_Group_References)[])
    }
})

export const Delete = restRequest<{}, iFeature_Group_References, {}, iDeleteC6RestResponse<iFeature_Group_References>, RestShortTableNames>(
    {
        C6: C6,
        tableName: feature_group_references.TABLE_NAME,
        requestMethod: DELETE,
        queryCallback: (request) => {
            request.success ??= 'Successfully removed the feature group references!'
            request.error ??= 'An unknown issue occurred removing the feature group references!'
            return request
        },
        responseCallback: (_response, request) => {
            // todo - request . where
            deleteRestfulObjectArrays<iFeature_Group_References>([
                request
            ], "feature_group_references", feature_group_references.PRIMARY_SHORT as (keyof iFeature_Group_References)[])
        }
    });


export default {
    Get, Post, Put, Delete
}
