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
import {C6, iReports, reports, RestShortTableNames} from "./C6";


export const Get = restRequest<{}, iReports, {}, iGetC6RestResponse<iReports>, RestShortTableNames>({
    C6: C6,
    tableName: reports.TABLE_NAME,
    requestMethod: GET,
    queryCallback: (request) => {
        request.success ??= 'Successfully received reports!'
        request.error ??= 'An unknown issue occurred creating the reports!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iReports>(response?.data?.rest, "reports", C6.reports.PRIMARY_SHORT as (keyof iReports)[])
    }
})

export const Put = restRequest<{}, iReports, {}, iPutC6RestResponse<iReports>, RestShortTableNames>({
    C6: C6,
    tableName: reports.TABLE_NAME,
    requestMethod: PUT,
    queryCallback: (request) => {
        request.success ??= 'Successfully updated reports!'
        request.error ??= 'An unknown issue occurred updating the reports!'
        return request
    },
    responseCallback: (response, request) => {
        updateRestfulObjectArrays<iReports>([
            removeInvalidKeys<iReports>({
                ...request,
                ...response?.data?.rest,
            }, C6.TABLES)
        ], "reports", reports.PRIMARY_SHORT as (keyof iReports)[])
    }
})


export const Post = restRequest<{}, iReports, {}, iPostC6RestResponse<iReports>, RestShortTableNames>({
    C6: C6,
    tableName: reports.TABLE_NAME,
    requestMethod: POST,
    queryCallback: (request) => {
        request.success ??= 'Successfully created the reports!'
        request.error ??= 'An unknown issue occurred creating the reports!'
        return request
    },
    responseCallback: (response, request, id) => {
        if ('number' === typeof id || 'string' === typeof id) {
            if (1 !== reports.PRIMARY_SHORT.length) {
                console.error("C6 received unexpected result's given the primary key length");
            } else {
                request[reports.PRIMARY_SHORT[0]] = id
            }
        }
        updateRestfulObjectArrays<iReports>(
            undefined !== request.dataInsertMultipleRows
                ? request.dataInsertMultipleRows.map((request, index) => {
                    return removeInvalidKeys<iReports>({
                        ...request,
                        ...(index === 0 ? response?.data?.rest : {}),
                    }, C6.TABLES)
                })
                : [
                    removeInvalidKeys<iReports>({
                        ...request,
                        ...response?.data?.rest,
                    }, C6.TABLES)
                ]
            , "reports", reports.PRIMARY_SHORT as (keyof iReports)[])
    }
})

export const Delete = restRequest<{}, iReports, {}, iDeleteC6RestResponse<iReports>, RestShortTableNames>(
    {
        C6: C6,
        tableName: reports.TABLE_NAME,
        requestMethod: DELETE,
        queryCallback: (request) => {
            request.success ??= 'Successfully removed the reports!'
            request.error ??= 'An unknown issue occurred removing the reports!'
            return request
        },
        responseCallback: (_response, request) => {
            // todo - request . where
            deleteRestfulObjectArrays<iReports>([
                request
            ], "reports", reports.PRIMARY_SHORT as (keyof iReports)[])
        }
    });


export default {
    Get, Post, Put, Delete
}
